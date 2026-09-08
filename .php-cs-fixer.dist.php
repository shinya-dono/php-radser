<?php

declare(strict_types = 1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

return new Config()
	->setFinder(new Finder()->in([__DIR__.'/src', __DIR__.'/tests']))
	->setParallelConfig(ParallelConfigFactory::detect())
	->setRiskyAllowed(true)
	->setLineEnding("\n")
	->setIndent("\t")
	->setRules(
		[
			'@auto'             => true,
			'@auto:risky'       => true,
			'@PhpCsFixer'       => true,
			'@PhpCsFixer:risky' => true,

			'control_structure_continuation_position' => [
				'position' => 'next_line',
			],

			'no_extra_blank_lines' => [
				'tokens' => [
					'attribute',
					'break',
					'case',
					'continue',
					'default',
					'extra',
					'parenthesis_brace_block',
					'return',
					'square_brace_block',
					'switch',
					'throw',
					'use',
				],
			],

			'declare_equal_normalize' => [
				'space' => 'single',
			],

			'fully_qualified_strict_types' => [
				'phpdoc_tags' => [],
			],

			'php_unit_test_case_static_method_calls'=> [
				'call_type' => 'this',
			],

			'binary_operator_spaces' => [
				'operators' => ['=>' => 'align'],
			],

			'ordered_imports' => [
				'sort_algorithm' => 'length',
			],

			'phpdoc_align' => [
				'align' => 'left',
				'tags'  => ['method', 'property', 'return', 'throws', 'type', 'var'],
			],

			'phpdoc_separation' => [
				'groups' => [
					['author', 'copyright', 'license'],
					['category', 'package', 'subpackage'],
					['property', 'property-read', 'property-write'],
					['deprecated', 'link', 'see', 'since'],
					['param'],
					['return'],
					['throws'],
				],
			],

			'trailing_comma_in_multiline' => [
				'elements' => ['arrays', 'arguments', 'parameters', 'match'],
			],

			'yoda_style' => true,

			'global_namespace_import' => [
				'import_classes'   => true,
				'import_constants' => null,
				'import_functions' => null,
			],

			'phpdoc_trim_consecutive_blank_line_separation' => true,

			'phpdoc_no_empty_return'    => false,
			'single_line_comment_style' => false,

			'nullable_type_declaration' => [
				'syntax' => 'union',
			],

			'braces_position' => [
				'allow_single_line_empty_anonymous_classes' => true,
				'allow_single_line_anonymous_functions'     => false,
				'control_structures_opening_brace'          => 'same_line',
				'anonymous_functions_opening_brace'         => 'next_line_unless_newline_at_signature_end',
				'anonymous_classes_opening_brace'           => 'next_line_unless_newline_at_signature_end',
				'functions_opening_brace'                   => 'next_line_unless_newline_at_signature_end',
				'classes_opening_brace'                     => 'next_line_unless_newline_at_signature_end',
			],

			'ordered_class_elements' => [
				'order'          => [
					'use_trait',
					'constant',
					'property_public_static',
					'property_protected_static',
					'property_private_static',
					'property_public',
					'property_protected',
					'property_private',
					'construct',
					'method_public_static',
					'method_protected_static',
					'method_private_static',
					'method_abstract',

					'magic',

					'method_public',
					'method_protected',
					'method_private',
				],
				'sort_algorithm' => 'none',
			],

			'blank_line_after_namespace' => true,
			'single_line_empty_body'     => true,
			'elseif'                     => true,
			'native_function_invocation' => false,

			'phpdoc_summary' => true,

			'explicit_string_variable' => false,

			'attribute_block_no_spaces' => true,

			'ordered_types' => [
				'null_adjustment' => 'always_last',
				'sort_algorithm'  => 'none',
			],

			'phpdoc_types_order' => [
				'null_adjustment' => 'always_last',
				'sort_algorithm'  => 'none',
			],
		],
	)
;
