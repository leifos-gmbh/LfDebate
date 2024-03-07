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

namespace ILIAS\UI\Implementation;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\UI\Implementation\Component\Input\Field\DebateInputRenderer;

class DebateUIRenderer extends DefaultRenderer
{
    /**
     * @var Container
     */
    protected $dic;

    public function __construct(Render\Loader $component_renderer_loader, Container $dic)
    {
        parent::__construct($component_renderer_loader);
        $this->dic = $dic;
    }

    protected function getRendererFor(Component $component)
    {
        if ($component instanceof Input) {
            $renderer = new DebateInputRenderer(
                $this->dic["ui.factory"],
                $this->dic["ui.template_factory"],
                $this->dic["lng"],
                $this->dic["ui.javascript_binding"],
                $this->dic["refinery"],
                $this->dic["ui.pathresolver"]
            );

            $registry = $this->dic["ui.resource_registry"];
            $renderer->registerResources($registry);

            return $renderer;
        }
        return parent::getRendererFor($component);
    }
}
