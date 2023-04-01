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

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Component\Dropzone\File\Wrapper as WrapperDropzone;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\File as FileInput;
use ILIAS\UI\Component\Component;

/**
 * @author  Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Wrapper extends File implements WrapperDropzone
{
    /**
     * @var Component[]
     */
    protected array $content;

    /**
     * @param Component|Component[]
     */
    public function __construct(
        SignalGeneratorInterface $signal_generator,
        FieldFactory $field_factory,
        NameSource $name_source,
        FileInput $file_input,
        string $title,
        $content,
        string $post_url
    ) {
        parent::__construct($signal_generator, $field_factory, $name_source, $file_input, $title, $post_url);

        $content = $this->toArray($content);
        $this->checkArgListElements('content', $content, [Component::class]);
        $this->content = $content;
    }

    /**
     * @return Component[]
     */
    public function getContent(): array
    {
        return $this->content;
    }
}
