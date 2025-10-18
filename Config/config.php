<?php
/**
 * Provide any configuration items here
 */

return [
    'name' => 'SPPassport',

    'passport_stamps' => [
        'name' => 'Passport Stamps',
        'class' => \Modules\SPPassport\Widgets\PassportStamps::class,
        'position' => 'left', // oder right, top, bottom
    ],
];
