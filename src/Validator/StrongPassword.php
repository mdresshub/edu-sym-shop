<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class StrongPassword extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public string $message = 'Das gewählte Passwort "{{ value }}" ist nicht stark genug. (min. 8 Zeichen, 1 Ziffer, 1 Sonderzeichen, 1 Groß- und 1 Kleinbuchstabe)';
}
