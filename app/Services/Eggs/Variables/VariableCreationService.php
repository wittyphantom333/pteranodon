<?php

namespace Pteranodon\Services\Eggs\Variables;

use Pteranodon\Models\EggVariable;
use Pteranodon\Traits\Services\ValidatesValidationRules;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Pteranodon\Contracts\Repository\EggVariableRepositoryInterface;
use Pteranodon\Exceptions\Service\Egg\Variable\ReservedVariableNameException;

class VariableCreationService
{
    use ValidatesValidationRules;

    /**
     * VariableCreationService constructor.
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
     * Create a new variable for a given Egg.
     *
     * @throws \Pteranodon\Exceptions\Model\DataValidationException
     * @throws \Pteranodon\Exceptions\Service\Egg\Variable\BadValidationRuleException
     * @throws \Pteranodon\Exceptions\Service\Egg\Variable\ReservedVariableNameException
     */
    public function handle(int $egg, array $data): EggVariable
    {
        if (in_array(strtoupper(array_get($data, 'env_variable')), explode(',', EggVariable::RESERVED_ENV_NAMES))) {
            throw new ReservedVariableNameException(sprintf('Cannot use the protected name %s for this environment variable.', array_get($data, 'env_variable')));
        }

        if (!empty($data['rules'] ?? '')) {
            $this->validateRules($data['rules']);
        }

        $options = array_get($data, 'options') ?? [];

        return $this->repository->create([
            'egg_id' => $egg,
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
