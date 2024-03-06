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

use ILIAS\DI\HTTPServices;
use ILIAS\Refinery;

class GUIRequest
{
    //use BaseGUIRequest;  // für ILIAS 9+

    /**
     * @var HTTPServices
     */
    protected $http;

    public function __construct(
        HTTPServices $http,
        Refinery\Factory $refinery,
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ) {
        // wir können noch nicht den request wrapper in ILIAS 7 benutzen
        /*
        $this->initRequest(
            $http,
            $refinery,
            $passed_query_params,
            $passed_post_data
        );
        */

        $this->http = $http;
    }

    public function getPostingId(): int
    {
        return (int) $this->http->request()->getQueryParams()["post_id"];
    }

    public function getCommentId(): int
    {
        return (int) $this->http->request()->getQueryParams()["cmt_id"];
    }

    public function getPostingMode(): bool
    {
        return (bool) $this->http->request()->getQueryParams()["post_mode"];
    }

    public function getSorting(): int
    {
        return (int) $this->http->request()->getQueryParams()["sortation"];
    }

    public function getContributor(): int
    {
        return (int) $this->http->request()->getQueryParams()["contrib"];
    }

    public function getResourceID(): string
    {
        return (string) $this->http->request()->getQueryParams()["rid"];
    }
}
