<?php
/*---------------------------------------------------------------------------------------------
 * Copyright (c) Microsoft Corporation. All rights reserved.
 *  Licensed under the MIT License. See License.txt in the project root for license information.
 *--------------------------------------------------------------------------------------------*/

namespace PhpParser\Node;

use PhpParser\NodeKind;
use PhpParser\Token;

class AssignmentExpression extends BinaryExpression {

    /** @var Expression */
    public $leftOperand;

    /** @var Token */
    public $operator;

    /** @var Token */
    public $byRef;

    /** @var Expression */
    public $rightOperand;

    public function __construct() {
        parent::__construct(NodeKind::AssignmentExpression);
    }
}