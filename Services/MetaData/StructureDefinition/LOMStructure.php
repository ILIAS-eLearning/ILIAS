<?php

use ILIAS\MetaData\Elements\Data\Type;

/**
 * COMMON SUB-ELEMENTS
 */
$langstring = [
    [
        'name' => 'string',
        'unique' => true,
        'type' => Type::STRING,
        'sub' => []
    ],
    [
        'name' => 'language',
        'unique' => true,
        'type' => Type::LANG,
        'sub' => []
    ]
];

$vocab = [
    [
        'name' => 'source',
        'unique' => true,
        'type' => Type::VOCAB_SOURCE,
        'sub' => []
    ],
    [
        'name' => 'value',
        'unique' => true,
        'type' => Type::VOCAB_VALUE,
        'sub' => []
    ]
];

$duration = [
    [
        'name' => 'duration',
        'unique' => true,
        'type' => Type::DURATION,
        'sub' => []
    ],
    [
        'name' => 'description',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $langstring
    ]
];

$datetime = [
    [
        'name' => 'dateTime',
        'unique' => true,
        'type' => Type::DATETIME,
        'sub' => []
    ],
    [
        'name' => 'description',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $langstring
    ]
];

/**
 * SECTIONS
 */
$general = [
    [
        'name' => 'identifier',
        'unique' => false,
        'type' => Type::NULL,
        'sub' => [
            [
                'name' => 'catalog',
                'unique' => true,
                'type' => Type::STRING,
                'sub' => []
            ],
            [
                'name' => 'entry',
                'unique' => true,
                'type' => Type::STRING,
                'sub' => []
            ]
        ]
    ],
    [
        'name' => 'title',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $langstring
    ],
    [
        'name' => 'language',
        'unique' => false,
        'type' => Type::LANG,
        'sub' => []
    ],
    [
        'name' => 'description',
        'unique' => false,
        'type' => Type::NULL,
        'sub' => $langstring
    ],
    [
        'name' => 'keyword',
        'unique' => false,
        'type' => Type::NULL,
        'sub' => $langstring
    ],
    [
        'name' => 'coverage',
        'unique' => false,
        'type' => Type::NULL,
        'sub' => $langstring
    ],
    [
        'name' => 'structure',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $vocab
    ],
    [
        'name' => 'aggregationLevel',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $vocab
    ]
];

$lifecycle = [
    [
        'name' => 'version',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $langstring
    ],
    [
        'name' => 'status',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $vocab
    ],
    [
        'name' => 'contribute',
        'unique' => false,
        'type' => Type::NULL,
        'sub' => [
            [
                'name' => 'role',
                'unique' => true,
                'type' => Type::NULL,
                'sub' => $vocab
            ],
            [
                'name' => 'entity',
                'unique' => false,
                'type' => Type::STRING,
                'sub' => []
            ],
            [
                'name' => 'date',
                'unique' => true,
                'type' => Type::NULL,
                'sub' => $datetime
            ]
        ]
    ]
];

$metametadata = [
    [
        'name' => 'identifier',
        'unique' => false,
        'type' => Type::NULL,
        'sub' => [
            [
                'name' => 'catalog',
                'unique' => true,
                'type' => Type::STRING,
                'sub' => []
            ],
            [
                'name' => 'entry',
                'unique' => true,
                'type' => Type::STRING,
                'sub' => []
            ]
        ]
    ],
    [
        'name' => 'contribute',
        'unique' => false,
        'type' => Type::NULL,
        'sub' => [
            [
                'name' => 'role',
                'unique' => true,
                'type' => Type::NULL,
                'sub' => $vocab
            ],
            [
                'name' => 'entity',
                'unique' => false,
                'type' => Type::STRING,
                'sub' => []
            ],
            [
                'name' => 'date',
                'unique' => true,
                'type' => Type::NULL,
                'sub' => $datetime
            ]
        ]
    ],
    [
        'name' => 'metadataSchema',
        'unique' => false,
        'type' => Type::STRING,
        'sub' => []
    ],
    [
        'name' => 'language',
        'unique' => true,
        'type' => Type::LANG,
        'sub' => []
    ]
];

$technical = [
    [
        'name' => 'format',
        'unique' => false,
        'type' => Type::STRING,
        'sub' => []
    ],
    [
        'name' => 'size',
        'unique' => true,
        'type' => Type::NON_NEG_INT,
        'sub' => []
    ],
    [
        'name' => 'location',
        'unique' => false,
        'type' => Type::STRING,
        'sub' => []
    ],
    [
        'name' => 'requirement',
        'unique' => false,
        'type' => Type::NULL,
        'sub' => [
            [
                'name' => 'orComposite',
                'unique' => false,
                'type' => Type::NULL,
                'sub' => [
                    [
                        'name' => 'type',
                        'unique' => true,
                        'type' => Type::NULL,
                        'sub' => $vocab
                    ],
                    [
                        'name' => 'name',
                        'unique' => true,
                        'type' => Type::NULL,
                        'sub' => $vocab
                    ],
                    [
                        'name' => 'minimumVersion',
                        'unique' => true,
                        'type' => Type::STRING,
                        'sub' => []
                    ],
                    [
                        'name' => 'maximumVersion',
                        'unique' => true,
                        'type' => Type::STRING,
                        'sub' => []
                    ]
                ]
            ]
        ]
    ],
    [
        'name' => 'installationRemarks',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $langstring
    ],
    [
        'name' => 'otherPlatformRequirements',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $langstring
    ],
    [
        'name' => 'duration',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $duration
    ]
];

$educational = [
    [
        'name' => 'interactivityType',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $vocab
    ],
    [
        'name' => 'learningResourceType',
        'unique' => false,
        'type' => Type::NULL,
        'sub' => $vocab
    ],
    [
        'name' => 'interactivityLevel',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $vocab
    ],
    [
        'name' => 'semanticDensity',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $vocab
    ],
    [
        'name' => 'intendedEndUserRole',
        'unique' => false,
        'type' => Type::NULL,
        'sub' => $vocab
    ],
    [
        'name' => 'context',
        'unique' => false,
        'type' => Type::NULL,
        'sub' => $vocab
    ],
    [
        'name' => 'typicalAgeRange',
        'unique' => false,
        'type' => Type::NULL,
        'sub' => $langstring
    ],
    [
        'name' => 'difficulty',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $vocab
    ],
    [
        'name' => 'typicalLearningTime',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $duration
    ],
    [
        'name' => 'description',
        'unique' => false,
        'type' => Type::NULL,
        'sub' => $langstring
    ],
    [
        'name' => 'language',
        'unique' => false,
        'type' => Type::LANG,
        'sub' => []
    ]
];

$rights = [
    [
        'name' => 'cost',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $vocab
    ],
    [
        'name' => 'copyrightAndOtherRestrictions',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $vocab
    ],
    [
        'name' => 'description',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $langstring
    ]
];

$relation = [
    [
        'name' => 'kind',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $vocab
    ],
    [
        'name' => 'resource',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => [
            [
                'name' => 'identifier',
                'unique' => false,
                'type' => Type::NULL,
                'sub' => [
                    [
                        'name' => 'catalog',
                        'unique' => true,
                        'type' => Type::STRING,
                        'sub' => []
                    ],
                    [
                        'name' => 'entry',
                        'unique' => true,
                        'type' => Type::STRING,
                        'sub' => []
                    ]
                ]
            ],
            [
                'name' => 'description',
                'unique' => false,
                'type' => Type::NULL,
                'sub' => $langstring
            ]
        ]
    ]
];

$annotation = [
    [
        'name' => 'entity',
        'unique' => true,
        'type' => Type::STRING,
        'sub' => []
    ],
    [
        'name' => 'date',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $datetime
    ],
    [
        'name' => 'description',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $langstring
    ]
];

$classification = [
    [
        'name' => 'purpose',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $vocab
    ],
    [
        'name' => 'taxonPath',
        'unique' => false,
        'type' => Type::NULL,
        'sub' => [
            [
                'name' => 'source',
                'unique' => true,
                'type' => Type::NULL,
                'sub' => $langstring
            ],
            [
                'name' => 'taxon',
                'unique' => false,
                'type' => Type::NULL,
                'sub' => [
                    [
                        'name' => 'id',
                        'unique' => true,
                        'type' => Type::STRING,
                        'sub' => []
                    ],
                    [
                        'name' => 'entry',
                        'unique' => true,
                        'type' => Type::NULL,
                        'sub' => $langstring
                    ]
                ]
            ]
        ]
    ],
    [
        'name' => 'description',
        'unique' => true,
        'type' => Type::NULL,
        'sub' => $langstring
    ],
    [
        'name' => 'keyword',
        'unique' => false,
        'type' => Type::NULL,
        'sub' => $langstring
    ]
];

/**
 * TOTAL STRUCTURE
 */
$structure = [
    'name' => 'lom',
    'unique' => true,
    'type' => Type::NULL,
    'sub' => [
        [
            'name' => 'general',
            'unique' => true,
            'type' => Type::NULL,
            'sub' => $general
        ],
        [
            'name' => 'lifeCycle',
            'unique' => true,
            'type' => Type::NULL,
            'sub' => $lifecycle
        ],
        [
            'name' => 'metaMetadata',
            'unique' => true,
            'type' => Type::NULL,
            'sub' => $metametadata
        ],
        [
            'name' => 'technical',
            'unique' => true,
            'type' => Type::NULL,
            'sub' => $technical
        ],
        [
            'name' => 'educational',
            'unique' => false,
            'type' => Type::NULL,
            'sub' => $educational
        ],
        [
            'name' => 'rights',
            'unique' => true,
            'type' => Type::NULL,
            'sub' => $rights
        ],
        [
            'name' => 'relation',
            'unique' => false,
            'type' => Type::NULL,
            'sub' => $relation
        ],
        [
            'name' => 'annotation',
            'unique' => false,
            'type' => Type::NULL,
            'sub' => $annotation
        ],
        [
            'name' => 'classification',
            'unique' => false,
            'type' => Type::NULL,
            'sub' => $classification
        ]
    ]
];

return $structure;
