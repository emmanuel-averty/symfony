<?php

namespace Symfony\Component\Console\Input;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolationList;

interface InputViolationsFormaterInterface
{
    /**
     * @param array<string, ConstraintViolationList> $argumentsViolations
     * @param array<string, ConstraintViolationList> $optionsViolations
     */
    public function outputViolations(InputInterface $input, OutputInterface $output, array $argumentsViolations, array $optionsViolations): void;
}