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

use ILIAS\Data\Factory;
use ILIAS\Refinery\Factory as Refinery;

class ilMimeMail
{
    final public const string MAIL_SUBJECT_PREFIX = '[ILIAS]';
    private const string SKIN_LOGO_PATH = '/public/Customizing/skin/%s/images/logo';
    private const string SKIN_CSS_PATH = '/public/Customizing/skin/%s/mail.css';
    private const string MAIL_CSS_PATH = 'assets/css/mail.css';
    private const string MAIL_LOGO_PATH = '/public/assets/images/logo/HeaderIcon.svg';
    private const string ROOT_DIR_IDENTIFICATION_FILE = '/ilias_version.php';

    protected static ?ilMailMimeTransport $default_transport = null;

    protected ilMailMimeSender $sender;
    protected ilMailMimeSubjectBuilder $subject_builder;
    protected ilSetting $settings;
    protected string $subject = '';
    protected string $body = '';
    protected string $final_body = '';
    protected string $final_body_alt = '';
    /** @var string[] */
    protected array $sendto = [];
    /** @var string[] */
    protected array $acc = [];
    /** @var string[] */
    protected array $abcc = [];
    /** @var array<string, array{path: string, cid: string, name: string, as_logo: bool}> */
    protected array $images = [];
    /** @var string[] */
    protected array $aattach = [];
    /** @var string[] */
    protected array $actype = [];
    /** @var string[] */
    protected array $adispo = [];
    /** @var string[] */
    protected array $adisplay = [];
    private readonly Refinery $refinery;
    /** @var Closure(string): string|null */
    private ?Closure $to_html_transformation = null;

    public function __construct()
    {
        global $DIC;
        $this->settings = $DIC->settings();

        if (!(self::getDefaultTransport() instanceof ilMailMimeTransport)) {
            $factory = $DIC->mail()->mime()->transportFactory();
            self::setDefaultTransport($factory->getTransport());
        }

        $this->subject_builder = new ilMailMimeSubjectBuilder($this->settings, self::MAIL_SUBJECT_PREFIX);
        $this->refinery = $DIC->refinery();
    }

    public static function setDefaultTransport(?ilMailMimeTransport $transport): void
    {
        self::$default_transport = $transport;
    }

    public static function getDefaultTransport(): ?ilMailMimeTransport
    {
        return self::$default_transport;
    }

    public function Subject(string $subject, bool $add_prefix = false, string $context_prefix = ''): void
    {
        $this->subject = $this->subject_builder->subject($subject, $add_prefix, $context_prefix);
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function From(ilMailMimeSender $sender): void
    {
        $this->sender = $sender;
    }

    /**
     * @param string|string[] $to To email address, accept both a single address or an array of addresses
     */
    public function To($to): void
    {
        if (is_array($to)) {
            $this->sendto = $to;
        } else {
            $this->sendto[] = $to;
        }
    }

    /**
     * @param string|string[] $cc CC email address, accept both a single address or an array of addresses
     */
    public function Cc($cc): void
    {
        if (is_array($cc)) {
            $this->acc = $cc;
        } else {
            $this->acc[] = $cc;
        }
    }

    /**
     * @param string|string[] $bcc BCC email address, accept both a single address or an array of addresses
     */
    public function Bcc($bcc): void
    {
        if (is_array($bcc)) {
            $this->abcc = $bcc;
        } else {
            $this->abcc[] = $bcc;
        }
    }

    /**
     * @return string[]
     */
    public function getTo(): array
    {
        return $this->sendto;
    }

    /**
     * @return string[]
     */
    public function getCc(): array
    {
        return $this->acc;
    }

    /**
     * @return string[]
     */
    public function getBcc(): array
    {
        return $this->abcc;
    }

    /**
     * @param Closure(string): string|null $to_html_transformation
     */
    public function Body(string $body, ?Closure $to_html_transformation = null): void
    {
        $this->body = $body;
        $this->to_html_transformation = $to_html_transformation;
    }

    public function getFinalBody(): string
    {
        return $this->final_body;
    }

    public function getFinalBodyalt(): string
    {
        return $this->final_body_alt;
    }

    public function getFrom(): ilMailMimeSender
    {
        return $this->sender;
    }

    /**
     * @param string $filename Path of the file to attach
     * @param string $file_type MIME-type of the file. default to 'application/x-unknown-content-type'
     * @param string $disposition Instruct the Mailclient to display the file if possible ("inline")
     *                            or always as a link ("attachment") possible values are "inline", "attachment"
     * @param string|null $display_name Filename to use in email (if different from source file)
     */
    public function Attach(
        string $filename,
        string $file_type = '',
        string $disposition = 'inline',
        ?string $display_name = null
    ): void {
        if ($file_type === '') {
            $file_type = 'application/octet-stream';
        }

        $this->aattach[] = $filename;
        $this->actype[] = $file_type;
        $this->adispo[] = $disposition;
        $this->adisplay[] = $display_name;
    }

    /**
     * @return array{path: string, name: string}[]
     */
    public function getAttachments(): array
    {
        $attachments = [];

        $i = 0;
        foreach ($this->aattach as $attachment) {
            $name = '';
            if (isset($this->adisplay[$i]) && is_string($this->adisplay[$i]) && $this->adisplay[$i] !== '') {
                $name = $this->adisplay[$i];
            }

            $attachments[] = [
                'path' => $attachment,
                'name' => $name
            ];
            ++$i;
        }

        return $attachments;
    }

    /**
     * @return array{path: string, cid: string, name: string}[] An array of images. Each element must container
     * to associative keys, 'path', 'cid' and 'name'
     */
    public function getImages(): array
    {
        return array_values($this->images);
    }

    protected function build(): void
    {
        global $DIC;

        $this->final_body_alt = '';
        $this->final_body = '';
        $this->images = [];

        if ($DIC->settings()->get('mail_send_html', '0')) {
            $skin = $DIC['ilClientIniFile']->readVariable('layout', 'skin');
            $style = $DIC['ilClientIniFile']->readVariable('layout', 'style');

            $data_factory = new Factory();
            $factory = $DIC->ui()->factory();
            $renderer = $DIC->ui()->renderer();

            $this->prepareHTMLBody();

            $page = $factory->layout()->page()->mail(
                $this->getStyleSheetPath($skin, $style),
                "cid:{$this->getLogoCid($skin, $style)}",
                ilObjSystemFolder::_getHeaderTitle(),
                $factory->legacy()->content($this->body),
                $data_factory->link(ilUtil::_getHttpPath(), $data_factory->uri(ilUtil::_getHttpPath())),
            );

            $this->final_body = $renderer->render($page);
            $this->final_body_alt = $this->removeHtmlTags($this->body);
        } else {
            $this->final_body = $this->removeHtmlTags($this->body);
        }
    }

    private function removeHtmlTags(string $maybe_html): string
    {
        $maybe_html = str_ireplace(['<br />', '<br>', '<br/>'], "\n", $maybe_html);

        return html_entity_decode(strip_tags($maybe_html), ENT_QUOTES);
    }

    private function getPathToRootDirectory(): string
    {
        $current_dir = realpath(__DIR__);

        while ($current_dir !== '.') {
            if (file_exists($current_dir . self::ROOT_DIR_IDENTIFICATION_FILE)) {
                break;
            }

            $current_dir = dirname($current_dir);
        }

        return $current_dir;
    }

    private function prepareHTMLBody(): void
    {
        if ($this->body === '') {
            $this->body = ' ';
        }

        $transformed_body = $this->to_html_transformation ? ($this->to_html_transformation)($this->body) : $this->body;

        $contains_html = $this->containsHtmlBlockElementsOrLineBreaks($transformed_body);
        if ($contains_html) {
            $this->final_body_alt = strip_tags(str_ireplace(['<br />', '<br>', '<br/>'], "\n", $this->body));
            $this->body = $transformed_body;
        } else {
            $this->final_body_alt = strip_tags($this->body);
            $this->body = nl2br($transformed_body);
        }

        $this->body = $this->refinery->string()->makeClickable()->transform($this->body);
    }

    private function containsHtmlBlockElementsOrLineBreaks(string $email_body): bool
    {
        if (str_contains($email_body, '<') === false || str_contains($email_body, '>') === false) {
            return false;
        }

        // Detect common HTML tags produced by Markdown rendering.
        $pattern = '~</?(p|br|div|ul|ol|li|code|pre|h[1-6])\b~i';
        if (preg_match($pattern, $email_body) === 1) {
            return true;
        }

        return strip_tags($email_body, '<b><u><i><a>') !== $email_body;
    }

    private function getStyleSheetPath(string $skin, string $style): string
    {
        if ($skin !== 'default') {
            $locations = [
                $skin,
                "$skin/$style"
            ];

            foreach ($locations as $location) {
                $custom_path = $this->getPathToRootDirectory() . sprintf(self::SKIN_CSS_PATH, $location);
                if (is_file($custom_path)) {
                    return $custom_path;
                }
            }
        }

        return self::MAIL_CSS_PATH;
    }

    private function getLogoCid(string $skin, string $style): string
    {
        if ($skin !== 'default') {
            $locations = [
                $skin,
                "$skin/$style"
            ];

            foreach ($locations as $location) {
                $custom_directory = $this->getPathToRootDirectory() . sprintf(self::SKIN_LOGO_PATH, $location);
                if (is_dir($custom_directory) && is_readable($custom_directory)) {
                    $this->gatherImagesFromDirectory($custom_directory);
                }
            }
        } else {
            $path = $this->getPathToRootDirectory() . self::MAIL_LOGO_PATH;
            if (is_file($path) && is_readable($path)) {
                return $this->addImage(new SplFileInfo($path), true);
            }
        }

        foreach ($this->images as $image) {
            if ($image['as_logo']) {
                return $image['cid'];
            }
        }

        $logo_cid = count($this->images) > 1 ? current($this->images)['cid'] : null;

        foreach ($this->images as $cid => $image) {
            $file_name = basename($image['path'], '.' . pathinfo($image['path'], PATHINFO_EXTENSION));
            if (in_array(strtolower($file_name), ['logo', 'headericon'], true)) {
                $logo_cid = $cid;
                break;
            }
        }

        if (is_string($logo_cid)) {
            $this->images[$logo_cid]['as_logo'] = true;
            return $logo_cid;
        }

        $path = $this->getPathToRootDirectory() . self::MAIL_LOGO_PATH;
        if (is_file($path) && is_readable($path)) {
            return $this->addImage(new SplFileInfo($path), true);
        }

        return '';
    }

    protected function gatherImagesFromDirectory(string $directory, bool $clear_previous = false): void
    {
        if ($clear_previous) {
            $this->images = [];
        }

        foreach (new RegexIterator(
            new DirectoryIterator($directory),
            '/\.(jpg|jpeg|gif|svg|png)$/i'
        ) as $file) {
            $this->addImage($file);
        }
    }

    private function addImage(SplFileInfo $file, bool $as_logo = false): string
    {
        $cid = 'img/' . $file->getFilename();

        $this->images[$cid] = [
            'path' => $file->getPathname(),
            'cid' => $cid,
            'name' => $file->getFilename(),
            'as_logo' => $as_logo
        ];

        return $cid;
    }

    public function Send(?ilMailMimeTransport $transport = null): bool
    {
        if (!($transport instanceof ilMailMimeTransport)) {
            $transport = self::getDefaultTransport();
        }

        $this->build();

        return $transport->send($this);
    }
}
