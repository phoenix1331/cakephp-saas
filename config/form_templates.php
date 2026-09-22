<?php
declare(strict_types=1);

/**
 * Tailwind styling for the FormHelper's default markup, shared by both
 * the admin dashboard and the public booking flow - see AppView::initialize().
 * The slot picker in the public booking flow builds its own radio markup
 * by hand rather than going through Form->control(), so it's unaffected
 * by the radio-related templates here.
 */
return [
    'input' => '<input type="{{type}}" name="{{name}}"{{attrs}} class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500">',
    'select' => '<select name="{{name}}"{{attrs}} class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500">{{content}}</select>',
    'textarea' => '<textarea name="{{name}}"{{attrs}} class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500">{{value}}</textarea>',
    'label' => '<label{{attrs}} class="mb-1 block text-sm font-medium text-gray-700">{{text}}</label>',
    'error' => '<div class="mt-1 text-sm text-red-600" id="{{id}}">{{content}}</div>',
    'formGroup' => '{{label}}{{input}}',
    'inputContainer' => '<div class="{{containerClass}} mb-4">{{content}}</div>',
    'inputContainerError' => '<div class="{{containerClass}} mb-4">{{content}}{{error}}</div>',
    'submitContainer' => '<div class="mt-2">{{content}}</div>',
    'checkboxWrapper' => '<div class="mb-2 flex items-center gap-2">{{input}}{{label}}</div>',
    'nestingLabel' => '{{hidden}}<label{{attrs}} class="flex items-center gap-2 text-sm text-gray-700">{{input}}{{text}}</label>',
    'checkbox' => '<input type="checkbox" name="{{name}}" value="{{value}}"{{attrs}} class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-500">',
    'multicheckboxWrapper' => '<fieldset{{attrs}} class="mb-4">{{content}}</fieldset>',
];
