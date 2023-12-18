<?php

namespace Pteranodon\Services\Eggs\Variables;

use Illuminate\Support\Str;
use Pteranodon\Models\EggVariable;
use Pteranodon\Exceptions\DisplayException;
use Pteranodon\Traits\Services\ValidatesValidationRules;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Pteranodon\Contracts\Repository\EggVariableRepositoryInterface;
use Pteranodon\Exceptions\Service\Egg\Variable\ReservedVariableNameException;

class VariableUpdateService
{
    use ValidatesValidationRules;

    /**
     * VariableUpdateService constructor.
     */
    public function __construct(private EggVariableRepositoryInterface $repository, private ValidationFactory $validator)
    {
    }

    /**
     * Return the validation factory instance to be used by rule validation
     * checking in the trait.
     */
    protected function getValidator(): ValidationFactory
    {
        return $this->validator;
    }

    /**
     * Update a specific egg variable.
     *
     * @throws \Pteranodon\Exceptions\DisplayException
     * @throws \Pteranodon\Exceptions\Model\DataValidationException
     * @throws \Pteranodon\Exceptions\Repository\RecordNotFoundException
     * @throws \Pteranodon\Exceptions\Service\Egg\Variable\ReservedVariableNameException
     */
    public function handle(EggVariable $variable, array $data): mixed
    {
        if (!is_null(array_get($data, 'env_variable'))) {
            if (in_array(strtoupper(array_get($data, 'env_variable')), explode(',', EggVariable::RESERVED_ENV_NAMES))) {
                throw new ReservedVariableNameException(trans('exceptions.service.variables.reserved_name', ['name' => array_get($data, 'env_variable')]));
            }

            $search = $this->repository->setColumns('id')->findCountWhere([
                ['env_variable', '=', $data['env_variable']],
                ['egg_id', '=', $variable->egg_id],
                ['id', '!=', $variable->id],
            ]);

            if ($search > 0) {
                throw new DisplayException(trans('exceptions.service.variables.env_not_unique', ['name' => array_get($data, 'env_variable')]));
            }
        }

        if (!empty($data['rules'] ?? '')) {
            $this->validateRules(
                (is_string($data['rules']) && Str::contains($data['rules'], ';;'))
                    ? explode(';;', $data['rules'])
                    : $data['rules']
            );
        }

        $options = array_get($data, 'options') ?? [];

        return $this->repository->withoutFreshModel()->update($variable->id, [
            'name' => $data['name'] ?? '',
            'description' => $data['description'] ?? '',
            'env_variable' => $data['env_variable'] ?? '',
            'default_value' => $data['default_value'] ?? '',
            'user_viewable' => in_array('user_viewable', $options),
            'user_editable' => in_array('user_editable', $options),
            'rules' => $data['rules'] ?? '',
        ]);
    }
}
