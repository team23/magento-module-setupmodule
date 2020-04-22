<?php

namespace Team23\SetupModule\Model\SetupResourceCreation;

/**
 * Interface CreatorInterface
 *
 * @package Team23\SetupModule\Model\SetupResourceCreation
 */
interface CreatorInterface
{
    /**
     * Validate necessary data for saving and throw Exceptions if validation fails
     *
     * @param array $data
     */
    function validate(array $data): void;

    /**
     * Save the resource with provided data
     *
     * @param array $data
     */
    function save(array $data): void;
}