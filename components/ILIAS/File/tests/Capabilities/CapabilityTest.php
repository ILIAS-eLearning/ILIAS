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

use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\File\Capabilities\CapabilityBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use ILIAS\WOPI\Discovery\ActionRepository;
use ILIAS\HTTP\Services;
use ILIAS\StaticURL\Builder\URIBuilder;
use PHPUnit\Framework\TestCase;
use ILIAS\File\Capabilities\Permissions;
use ILIAS\File\Capabilities\Capabilities;
use ILIAS\File\Capabilities\TypeResolver;
use ILIAS\File\Capabilities\Context;

class CapabilityTest extends TestCase
{
    /**
     * @var (\ilWorkspaceAccessHandler & \PHPUnit\Framework\MockObject\MockObject)
     */
    public MockObject $workspace_access_handler;
    public MockObject $type_resolver;
    private MockObject $file_info_repository;
    private MockObject $access;
    private MockObject $action_repository;
    private CapabilityBuilder $capability_builder;

    private static array $readme_infos = [];

    private static bool $update_readme = false;

    protected function setUp(): void
    {
        if (!defined('ILIAS_HTTP_PATH')) {
            define('ILIAS_HTTP_PATH', 'https://ilias.unit.test');
        }

        $this->file_info_repository = $this->createMock(\ilObjFileInfoRepository::class);
        $this->access = $this->createMock(\ilAccessHandler::class);
        $ctrl = $this->createMock(\ilCtrlInterface::class);
        $this->action_repository = $this->createMock(ActionRepository::class);
        $http = $this->createMock(Services::class);
        $static_url = $this->createMock(URIBuilder::class);
        $this->type_resolver = $this->createMock(TypeResolver::class);
        $this->workspace_access_handler = $this->createMock(ilWorkspaceAccessHandler::class);

        $this->type_resolver->method('resolveTypeByObjectId')
                            ->withAnyParameters()
                            ->willReturn('file');

        $this->capability_builder = new CapabilityBuilder(
            $this->file_info_repository,
            $this->access,
            $ctrl,
            $this->action_repository,
            $http,
            $static_url,
            $this->type_resolver,
            $this->workspace_access_handler
        );
    }

    protected function tearDown(): void
    {
    }

    public static function tearDownAfterClass(): void
    {
        if (!self::$update_readme) {
            return;
        }
        self::updateREADME();
    }

    public static function environmentProvider(): \Iterator
    {
        yield 'testerei' => [
            true,
            true,
            true,
            [
                Permissions::READ,
                Permissions::WRITE,
                Permissions::VISIBLE,
                Permissions::EDIT_CONTENT,
                Permissions::VIEW_CONTENT
            ],
            Capabilities::FORCED_INFO_PAGE
        ];
        yield [
            true,
            true,
            false,
            [
                Permissions::READ,
                Permissions::WRITE,
                Permissions::VISIBLE,
                Permissions::EDIT_CONTENT,
                Permissions::VIEW_CONTENT
            ],
            Capabilities::VIEW_EXTERNAL
        ];
        yield [
            true,
            true,
            false,
            [
                Permissions::EDIT_CONTENT,
                Permissions::VIEW_CONTENT
            ],
            Capabilities::VIEW_EXTERNAL
        ];
        yield [
            false,
            false,
            true,
            [
                Permissions::READ,
                Permissions::VISIBLE
            ],
            Capabilities::FORCED_INFO_PAGE
        ];
        yield [
            true,
            true,
            false,
            [
                Permissions::EDIT_CONTENT,
            ],
            Capabilities::EDIT_EXTERNAL
        ];
        yield [
            true,
            true,
            false,
            [
                Permissions::READ,
            ],
            Capabilities::DOWNLOAD
        ];
        yield [
            true,
            true,
            false,
            [
                Permissions::WRITE,
                Permissions::READ,
            ],
            Capabilities::DOWNLOAD
        ];
        yield [
            true,
            true,
            false,
            [
                Permissions::WRITE,
            ],
            Capabilities::MANAGE_VERSIONS
        ];
        yield [
            true,
            true,
            false,
            [
                Permissions::VISIBLE,
            ],
            Capabilities::INFO_PAGE
        ];
        yield [
            true,
            true,
            true,
            [
                Permissions::WRITE,
                Permissions::READ,
            ],
            Capabilities::FORCED_INFO_PAGE
        ];
        yield [
            true,
            true,
            false,
            [
                Permissions::NONE,
            ],
            Capabilities::NONE
        ];
    }

    #[DataProvider('environmentProvider')]
    public function testCapabilityPriority(
        bool $wopi_view,
        bool $wopi_edit,
        bool $infopage_first,
        array $permissions,
        Capabilities $expected_best
    ): void {
        static $id;

        $id++;

        $context = new Context(
            $id,
            $id,
            Context::CONTEXT_REPO
        );

        $this->access->method('checkAccess')
                     ->willReturnCallback(
                         function (string $permission) use ($permissions): bool {
                             $checked_permissions = explode(',', $permission);
                             $common_permissions = array_intersect(
                                 array_map(static fn(Permissions $p): string => $p->value, $permissions),
                                 $checked_permissions
                             );
                             return $common_permissions !== [];
                         }
                     );

        $file_info = $this->createMock(\ilObjFileInfo::class);
        $file_info->method('shouldDownloadDirectly')
                  ->willReturn(!$infopage_first);

        $this->file_info_repository->method('getByObjectId')
                                   ->with($context->getObjectId())
                                   ->willReturn($file_info);

        $this->action_repository->method('hasEditActionForSuffix')
                                ->willReturn($wopi_edit);

        $this->action_repository->method('hasViewActionForSuffix')
                                ->willReturn($wopi_view);

        $capabilities = $this->capability_builder->get($context);
        $best = $capabilities->getBest();

        $this->assertEquals($expected_best, $best->getCapability());

        self::$readme_infos[] = [
            implode(', ', array_map(fn(Permissions $p): string => $p->value, $permissions)), // permissions
            ($wopi_view ? 'Yes' : 'No'),
            ($wopi_edit ? 'Yes' : 'No'),
            ($infopage_first ? 'Info-Page' : 'Open'),
            $best->getCapability()->name
        ];
    }

    private static function updateREADME(): void
    {
        // UPDATE README
        $readme_file = __DIR__ . '/../../docs/README.md';
        $readme_content = file_get_contents($readme_file);

        $table = [
            [
                'User\'s Permissions',
                'WOPI View Action av.',
                'WOPI Edit Action av.',
                'Click-Setting',
                'Expected Capability'
            ]
        ];
        $readme_infos = self::$readme_infos;
        // sort $readme_infos by last column
        usort($readme_infos, static function ($a, $b): int {
            $a_string = implode('', array_reverse($a));
            $b_string = implode('', array_reverse($b));

            return strcmp($a_string, $b_string);
        });

        $table = array_merge($table, $readme_infos);

        // Define the markers for the block
        $start_marker = "<!-- START CAPABILITY_TABLE -->";
        $end_marker = "<!-- END CAPABILITY_TABLE -->";

        // Prepare the new block content
        $new_block = $start_marker . "\n\n" . self::arrayToMarkdownTable($table) . "\n\n" . $end_marker;

        // Replace the content between the markers
        $pattern = '/' . preg_quote($start_marker, '/') . '.*?' . preg_quote($end_marker, '/') . '/s';
        $readme_content = preg_replace($pattern, $new_block, $readme_content);

        file_put_contents($readme_file, $readme_content);
    }

    private static function arrayToMarkdownTable(array $data): string
    {
        // Check if the input array is valid
        if (empty($data) || !is_array($data[0])) {
            throw new InvalidArgumentException("Input must be a non-empty array of arrays.");
        }

        // Calculate the maximum width of each column
        $col_widths = array_map(
            static fn(int|string $col_index): int => max(
                array_map(
                    static fn(array $row): int => isset($row[$col_index]) ? mb_strlen((string) $row[$col_index]) : 0,
                    $data
                )
            ),
            array_keys($data[0])
        );

        // Function to pad a row's columns to match the maximum width
        $pad_row = static fn($row): array => array_map(static function ($value, $index) use ($col_widths): string {
            $value ??= ''; // Handle missing values
            return str_pad($value, $col_widths[$index], " ", STR_PAD_RIGHT);
        }, $row, array_keys($col_widths));

        // Format the header and rows
        $header = $pad_row($data[0]);
        $rows = array_map($pad_row, array_slice($data, 1));

        // Build the Markdown table
        $header_row = "| "
            . implode(" | ", $header)
            . " |";
        $sep_row = "| "
            . implode(" | ", array_map(static fn(int $width): string => str_repeat("-", $width), $col_widths))
            . " |";
        $data_rows = array_map(static fn($row): string => "| " . implode(" | ", $row) . " |", $rows);

        // Combine all parts
        return implode("\n", array_merge([$header_row, $sep_row], $data_rows));
    }

}
