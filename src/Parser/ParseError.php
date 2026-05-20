<?php

declare(strict_types=1);

/**
 * Copyright 2026 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category  Horde
 * @copyright 2026 The Horde Project
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Icalendar
 */

namespace Horde\Icalendar\Parser;

enum ParseError: string
{
    case MissingColon = 'missing_colon';
    case InvalidName = 'invalid_name';
    case InvalidParamName = 'invalid_param_name';
    case UnterminatedQuotedString = 'unterminated_quoted_string';
    case InvalidParamValue = 'invalid_param_value';
    case EmptyLine = 'empty_line';
}
