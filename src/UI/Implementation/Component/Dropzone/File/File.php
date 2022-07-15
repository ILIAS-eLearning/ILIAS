<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Implementation\Component\Input\Field\FileUploadHelper;
use ILIAS\UI\Component\Dropzone\File\File as FileInterface;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\UI\Component\Signal;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Refinery\Transformation;
use ilLanguage;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class File implements FileInterface
{
    public const FILE_INPUT_KEY = 'files';
    protected const JAVASCRIPT_EVENT = 'drop';

    use FileUploadHelper;
    use JavaScriptBindable;
    use ComponentHelper;
    use Triggerer;

    /**
     * @var Transformation[]
     */
    protected array $operations = [];

    protected ?ServerRequestInterface $request = null;
    protected InputFactory $input_factory;
    protected ?Input $metadata_input;
    protected ilLanguage $language;
    protected ?string $error = null;
    protected string $title = '';
    protected string $post_url;

    public function __construct(
        InputFactory $input_factory,
        ilLanguage $language,
        UploadHandler $upload_handler,
        string $post_url,
        ?Input $metadata_input = null
    ) {
        $this->max_file_size = $this->getMaxFileSizeDefault();
        $this->input_factory = $input_factory;
        $this->language = $language;
        $this->upload_handler = $upload_handler;
        $this->post_url = $post_url;
        $this->metadata_input = $metadata_input;
    }

    // ==========================================
    // BEGIN IMPLEMENTATION OF FileInterface
    // ==========================================

    public function withTitle(string $title) : self
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function withOnDrop(Signal $signal) : self
    {
        return $this->withTriggeredSignal($signal, self::JAVASCRIPT_EVENT);
    }

    public function withAdditionalDrop(Signal $signal) : self
    {
        return $this->appendTriggeredSignal($signal, self::JAVASCRIPT_EVENT);
    }

    // ==========================================
    // END IMPLEMENTATION OF FileInterface
    // ==========================================

    // ==========================================
    // BEGIN IMPLEMENTATION OF Form
    // ==========================================

    public function getForm() : Form
    {
        $form = $this->input_factory
            ->container()
            ->form()
            ->standard(
                $this->post_url,
                $this->getInputs()
            );

        foreach ($this->operations as $trafo) {
            $form = $form->withAdditionalTransformation($trafo);
        }

        return $form;
    }

    /**
     * @inheritDoc
     */
    public function getInputs() : array
    {
        return [
            self::FILE_INPUT_KEY => $this->input_factory
                ->field()->file(
                    $this->upload_handler,
                    '',
                    null,
                    $this->metadata_input
                )->withMaxFiles($this->getMaxFiles())
                 ->withMaxFileSize($this->getMaxFileSize())
                 ->withAcceptedMimeTypes($this->getAcceptedMimeTypes())
            ,
        ];
    }

    public function withRequest(ServerRequestInterface $request) : self
    {
        $clone = clone $this;
        $clone->request = $request;

        return $clone;
    }

    public function withAdditionalTransformation(Transformation $trafo) : self
    {
        $clone = clone $this;
        $clone->operations[] = $trafo;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        if (null === $this->request) {
            throw new \LogicException("Cannot retrieve data without calling withRequest() first.");
        }

        $form = $this->getForm()->withRequest($this->request);
        $data = $form->getData();

        if (null !== $data) {
            return $data[self::FILE_INPUT_KEY] ?? null;
        }

        return null;
    }

    public function getError() : ?string
    {
        if (null === $this->request) {
            return null;
        }

        // we need to call getData() in order to set the error on $form.
        $form = $this->getForm()->withRequest($this->request);
        $data = $form->getData();

        return $form->getError();
    }

    // ==========================================
    // END IMPLEMENTATION OF Form
    // ==========================================
}
