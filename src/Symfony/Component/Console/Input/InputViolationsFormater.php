<?php

namespace Symfony\Component\Console\Input;

use Symfony\Component\Console\Output\OutputInterface;

class InputViolationsFormater implements InputViolationsFormaterInterface
{
    public function outputViolations(InputInterface $input, OutputInterface $output, array $argumentsViolations, array $optionsViolations): void
    {
        $this->outputInputViolations($output, 'argument', $input->getArguments(), $argumentsViolations);
        $this->outputInputViolations($output, 'option', $input->getOptions(), $optionsViolations);
    }

    private function outputInputViolations(OutputInterface $output, string $inputType, array $givenInputs, array $inputViolations): void
    {
        foreach ($inputViolations as $inputName => $violations) {
            if (count($violations) === 0) {
                continue;
            }

            foreach ($violations as $violation) {
                $messages = [];
                $messages[] = \sprintf(
                    '[error] %s %s has invalid value : %s',
                    $inputType,
                    $inputName,
                    is_array($givenInputs[$inputName]) ? implode(' ', $givenInputs[$inputName]) : $givenInputs[$inputName],
                );
                $messages[] = $emptyLine = \sprintf('<error>  %s  </error>', str_repeat(' ', strlen($violation->getMessage())));
                $messages[] = sprintf('<error>  %s  </error>', $violation->getMessage());
                $messages[] = $emptyLine;
                $messages[] = '';

                $output->writeln($messages);
            }
        }
    }
}