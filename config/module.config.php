<?php
namespace Storymap;

return [
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'block_layouts' => [
        'factories' => [
            'storymap' => Service\BlockLayout\StorymapFactory::class,
        ],
    ],
    'form_elements' => [
        'invokables' => [
            Form\StorymapBlockForm::class => Form\StorymapBlockForm::class,
        ],
        'factories' => [
            Form\ConfigForm::class => Service\Form\ConfigFormFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\StorymapController::class => Service\Controller\StorymapControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            'storymapData' => Mvc\Controller\Plugin\StorymapData::class,
        ],
    ],
    'router' => [
        'routes' => [
            // TODO Replace the storymap block route by a site and admin child routes?
            'storymap-block' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/storymap/:block-id/events.json',
                    'constraints' => [
                        'block-id' => '\d+',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => 'Storymap\Controller',
                        'controller' => 'StorymapController',
                        'action' => 'events',
                    ],
                ],
            ],
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'storymap' => [
        'settings' => [
            // Can be 'simile' or 'knightlab'.
            'storymap_library' => 'simile',
            'storymap_internal_assets' => false,
            'storymap_defaults' => [
                'item_title' => 'dcterms:title',
                'item_description' => 'dcterms:description',
                'item_date' => 'dcterms:date',
                'item_date_end' => '',
                // 'render_year' => \Storymap\Mvc\Controller\Plugin\StorymapData::RENDER_YEAR_DEFAULT,
                'render_year' => 'january_1',
                'center_date' => '9999-99-99',
                'viewer' => '{}',
                // The id of dcterms:date in the standard install of Omeka S.
                'item_date_id' => '7',
            ],
        ],
    ],
];
