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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Render\ResourceRegistry;

class DebateInputRenderer extends Renderer
{
    public function registerResources(ResourceRegistry $registry)
    {
        $registry->register('./src/UI/templates/js/Core/ui.js');
        $registry->register('./node_modules/moment/min/moment-with-locales.min.js');
        $registry->register('./node_modules/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js');

        $registry->register('./node_modules/@yaireo/tagify/dist/tagify.min.js');
        $registry->register('./node_modules/@yaireo/tagify/dist/tagify.css');
        $registry->register('./src/UI/templates/js/Input/Field/tagInput.js');

        $registry->register('./src/UI/templates/js/Input/Field/textarea.js');
        $registry->register('./src/UI/templates/js/Input/Field/input.js');
        $registry->register('./src/UI/templates/js/Input/Field/duration.js');
        $registry->register('./node_modules/dropzone/dist/min/dropzone.min.js');
        $registry->register('./Customizing/global/plugins/Services/Repository/RepositoryObject/LfDebate/js/xdbtFile.js');
        $registry->register('./src/UI/templates/js/Input/Field/groups.js');
    }
}
