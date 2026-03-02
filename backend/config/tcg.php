<?php

return [
    /*
     * Gate 3: Número mínimo de módulos validados para poder marcar
     * system_architecture como "done".
     */
    'min_validated_modules' => env('TCG_MIN_VALIDATED_MODULES', 3),
];
