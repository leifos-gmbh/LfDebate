<?php

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
 *********************************************************************/

declare(strict_types=1);

namespace Leifos\Debate;


class Sorting
{
    public const NAME_ASC = 1;
    public const NAME_DESC = 2;
    public const CREATION_ASC = 3;
    public const CREATION_DESC = 4;
    public const UPDATE_ASC = 5;
    public const UPDATE_DESC = 6;
    public const COMMENTS_ASC = 7;
    public const COMMENTS_DESC = 8;
    /**
     * @var \ilObjLfDebate
     */
    protected $debate;
    /**
     * @var \ilRepositoryObjectPlugin
     */
    protected $plugin;
    /**
     * @var \ilLanguage
     */
    protected $lng;
    /**
     * @var DomainFactory
     */
    protected $domain;

    public function __construct(
        DomainFactory $domain,
        \ilObjLfDebate $debate
    ) {
        $this->domain = $domain;
        $this->lng = $domain->lng();
        $this->plugin = $domain->plugin();
        $this->debate = $debate;
    }

    public function getAllOptions() : array
    {
        return [
            self::NAME_ASC => $this->plugin->txt("sort_name_asc"),
            self::NAME_DESC => $this->plugin->txt("sort_name_desc"),
            self::CREATION_ASC => $this->plugin->txt("sort_creation_asc"),
            self::CREATION_DESC => $this->plugin->txt("sort_creation_desc"),
            self::UPDATE_ASC => $this->plugin->txt("sort_update_asc"),
            self::UPDATE_DESC => $this->plugin->txt("sort_update_desc"),
            self::COMMENTS_ASC => $this->plugin->txt("sort_comments_asc"),
            self::COMMENTS_DESC => $this->plugin->txt("sort_comments_desc")
        ];
    }

    public function getSortLabel(int $sort)
    {
        $all = $this->getAllOptions();
        return $all[$sort] ?? "";
    }

    public function setCurrentSorting(int $sort) : void
    {
        \ilSession::set("xdbt_sort", (string) $sort);
    }

    public function getCurrentSorting() : int
    {
        $sort = (int) \ilSession::get("xdbt_sort");
        if ($sort === 0) {
            $sort = $this->debate->getDefaultSortation();
        }
        return $sort;
    }

}
