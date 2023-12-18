<?php

namespace Pteranodon\Http\Controllers\Auth;

use Pteranodon\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Pteranodon\Exceptions\DisplayException;
use Pteranodon\Http\Controllers\Controller;
use Pteranodon\Services\Users\UserCreationService;
use Pteranodon\Exceptions\Model\DataValidationException;
use Pteranodon\Contracts\Repository\SettingsRepositoryInterface;

class DiscordController extends Controller
{
    private SettingsRepositoryInterface $settings;
    private UserCreationService $creationService;

    public function __construct(
        UserCreationService $creationService,
        SettingsRepositoryInterface $settings,
    ) {
        $this->creationService = $creationService;
        $this->settings = $settings;
    }

    /**
     * Uses the Discord API to return a user objext.
     */
    public function index(): JsonResponse
    {
        return new JsonResponse([
            'https://discord.com/api/oauth2/authorize?'
            . 'client_id=' . $this->settings->get('pteranodon::discord:id')
            . '&redirect_uri=' . route('auth.discord.callback')
            . '&response_type=code&scope=identify%20email%20guilds%20guilds.join&prompt=none',
        ], 200, [], null, false);
    }

    /**
     * Returns data from the Discord API to login.
     *
     * @throws DisplayException
     * @throws DataValidationException
     */
    public function callback(Request $request)
    {
        $code = Http::asForm()->post('https://discord.com/api/oauth2/token', [
            'client_id' => $this->settings->get('pteranodon::discord:id'),
            'client_secret' => $this->settings->get('pteranodon::discord:secret'),
            'grant_type' => 'authorization_code',
            'code' => $request->input('code'),
            'redirect_uri' => route('auth.discord.callback'),
        ]);

        if (!$code->ok()) {
            return;
        }

        $req = json_decode($code->body());
        if (preg_match('(email|guilds|identify|guilds.join)', $req->scope) !== 1) {
            return;
        }

        $discord = json_decode(Http::withHeaders(['Authorization' => 'Bearer ' . $req->access_token])->asForm()->get('https://discord.com/api/users/@me')->body());

        if (User::where('discord_id', $discord->id)->exists()) {
            $user = User::where('discord_id', $discord->id)->first();
            Auth::loginUsingId($user->id, true);

            return redirect('/');
        } else {
            $approved = true;

            if ($this->settings->get('pteranodon::discord:enabled') != 'true') {
                return;
            }
            if ($this->settings->get('pteranodon::approvals:enabled') == 'true') {
                $approved = false;
            }

            $data = [
                'approved' => $approved,
                'email' => $discord->email,
                'username' => $discord->username,
                'discord_id' => $discord->id,
                'name_first' => $discord->username,
                'name_last' => $discord->discriminator,
                'password' => $this->genString(),
                'ip' => $request->getClientIp(),
                'store_cpu' => $this->settings->get('pteranodon::registration:cpu', 0),
                'store_memory' => $this->settings->get('pteranodon::registration:memory', 0),
                'store_disk' => $this->settings->get('pteranodon::registration:disk', 0),
                'store_slots' => $this->settings->get('pteranodon::registration:slot', 0),
                'store_ports' => $this->settings->get('pteranodon::registration:port', 0),
                'store_backups' => $this->settings->get('pteranodon::registration:backup', 0),
                'store_databases' => $this->settings->get('pteranodon::registration:database', 0),
            ];

            try {
                $this->creationService->handle($data);
            } catch (\Exception $e) {
                return;
            }
            $user = User::where('username', $discord->username)->first();
            Auth::loginUsingId($user->id, true);

            return redirect('/');
        }
    }

    /**
     * Returns a string used for creating a users
     * username and password on the Panel.
     */
    public function genString(): string
    {
        $chars = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        return substr(str_shuffle($chars), 0, 16);
    }
}
