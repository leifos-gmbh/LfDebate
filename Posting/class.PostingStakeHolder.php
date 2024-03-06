<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

namespace Leifos\Debate;

use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class PostingStakeHolder extends AbstractResourceStakeholder
{
    /**
     * @var int
     */
    protected $owner = 6;

    public function __construct(int $owner = 6)
    {
        $this->owner = $owner;
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return "lf_debate_posting";
    }

    /**
     * @inheritDoc
     */
    public function getOwnerOfNewResources(): int
    {
        return $this->owner;
    }
}
