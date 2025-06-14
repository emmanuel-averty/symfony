<?php

namespace Symfony\Component\Console\EventListener;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputViolationsFormaterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InputValidateListener implements EventSubscriberInterface
{
    public function __construct(
        private ?ValidatorInterface $validator,
        private InputViolationsFormaterInterface $inputViolationsFormater,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => ['onConsoleInputValidate', -128],
        ];
    }

    public function onConsoleInputValidate(ConsoleCommandEvent $event): void
    {
        if (null === $this->validator) {
            return;
        }

        $input = $event->getInput();
        $output = $event->getOutput();
        $definition = $event->getCommand()->getDefinition();

        if (
            array_reduce($definition->getArguments(), fn(int $count, InputArgument $argument) => $count + count($argument->getConstraints()), 0) +
            array_reduce($definition->getOptions(), fn(int $count, InputOption $option) => $count + count($option->getConstraints()), 0) === 0
        ) {
            // No constraints on arguments neither on options
            return;
        }

        // Since arguments and options could have the same name, we need to manage one variable for each
        $argumentsViolations = $this->validate($definition->getArguments(), $input->getArguments());
        $optionsViolations = $this->validate($definition->getOptions(), $input->getOptions());

        $violationCount =
            array_reduce($argumentsViolations, fn($count, $violations) => $count + count($violations), 0) +
            array_reduce($optionsViolations, fn($count, $violations) => $count + count($violations), 0)
        ;


        if ($violationCount > 0) {
            // At least one violation has been found
            $this->inputViolationsFormater->outputViolations($input, $output, $argumentsViolations, $optionsViolations);

            $event->disableCommand();
        }
    }

    /**
     * @param list<InputArgument>|list<InputOption> $inputs
     *
     * @return array<string, ConstraintViolationList>
     */
    private function validate($inputs, $givenInputs): array
    {
        $violations = [];
        foreach ($inputs as $input) {
            if (count($input->getConstraints()) === 0) {
                continue;
            }

            if (!array_key_exists($input->getName(), $givenInputs)) {
                continue;
            }

            $violations[$input->getName()] = $this->validator->validate($givenInputs[$input->getName()], $input->getConstraints());
        }

        return $violations;
    }
}