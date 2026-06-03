<?php

/**
 * Aircraft Economy Sections Configuration
 * ========================================
 * Defines economy sections for batch input, matching the partial layouts EXACTLY.
 * Each section has: name, rows (array), columns (array)
 *
 * VERIFIED against partial files on 2026-02-05
 */

return [
    // ═══════════════════════════════════════════════════════════
    // B737 Layouts (Single section, 3-3: A B C - H J K)
    // ═══════════════════════════════════════════════════════════
    'b737-e46' => [
        ['name' => 'Economy Class - Rows 21-46', 'rows' => array_values(array_diff(range(21, 46), [24])), 'columns' => ['A', 'B', 'C', 'H', 'J', 'K']],
    ],
    'b737-e47' => [
        ['name' => 'Economy Class - Rows 21-47', 'rows' => array_values(array_diff(range(21, 47), [24])), 'columns' => ['A', 'B', 'C', 'H', 'J', 'K']],
    ],
    'b737-e48' => [
        ['name' => 'Economy Class - Rows 21-48', 'rows' => array_values(array_diff(range(21, 48), [24])), 'columns' => ['A', 'B', 'C', 'H', 'J', 'K']],
    ],
    'b737-e49' => [
        ['name' => 'Economy Class - Rows 21-47', 'rows' => array_values(array_diff(range(21, 47), [24])), 'columns' => ['A', 'B', 'C', 'H', 'J', 'K']],
    ],

    // ═══════════════════════════════════════════════════════════
    // B777 Layouts (3-3-3: A B C - D F G - H J K)
    // ═══════════════════════════════════════════════════════════
    'b777-2class' => [
        ['name' => 'Economy Class - Rows 21-36', 'rows' => array_values(array_diff(range(21, 36), [24])), 'columns' => ['A', 'B', 'C', 'D', 'F', 'G', 'H', 'J', 'K']],
        ['name' => 'Economy Class - Rows 37-49', 'rows' => range(37, 49), 'columns' => ['A', 'B', 'C', 'D', 'F', 'G', 'H', 'J', 'K']],
        ['name' => 'Economy Class - Rows 50-63', 'rows' => range(50, 63), 'columns' => ['A', 'B', 'C', 'D', 'F', 'G', 'H', 'J', 'K']],
    ],
    'b777-3class' => [
        ['name' => 'Economy Class - Rows 21-25', 'rows' => [21, 22, 23, 25], 'columns' => ['A', 'B', 'C', 'D', 'F', 'G', 'H', 'J', 'K']],
        ['name' => 'Economy Class - Rows 26-38', 'rows' => range(26, 38), 'columns' => ['A', 'B', 'C', 'D', 'F', 'G', 'H', 'J', 'K']],
        ['name' => 'Economy Class - Rows 39-52', 'rows' => range(39, 52), 'columns' => ['A', 'B', 'C', 'D', 'F', 'G', 'H', 'J', 'K']],
    ],

    // ═══════════════════════════════════════════════════════════
    // A330-300 Layouts (2-4-2: A C - D E F G - H K)
    // ═══════════════════════════════════════════════════════════
    'a330-300a' => [
        ['name' => 'Economy Class - Rows 21-39', 'rows' => array_values(array_diff(range(21, 39), [24])), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K']],
        ['name' => 'Economy Class - Rows 40-55', 'rows' => range(40, 55), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K']],
    ],
    'a330-300b' => [
        ['name' => 'Economy Class - Rows 21-33', 'rows' => array_values(array_diff(range(21, 33), [24])), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K']],
        ['name' => 'Economy Class - Rows 34-49', 'rows' => range(34, 49), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K']],
    ],
    'a330-300c' => [
        ['name' => 'Economy Class - Rows 21-34', 'rows' => array_values(array_diff(range(21, 34), [24])), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K']],
        ['name' => 'Economy Class - Rows 35-54', 'rows' => range(35, 54), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K'], 'exceptions' => ['35A', '35C', '35D', '35E', '35F', '35G', '36D', '36E', '36F', '36G']],
        ['name' => 'Economy Class - Rows 55-70', 'rows' => range(55, 70), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K'], 'exceptions' => ['55D', '55E', '55F', '55G', '56D', '56E', '56F', '56G']],
    ],
    'a330-300cargo' => [
        ['name' => 'Economy Class - Rows 21-33', 'rows' => array_values(array_diff(range(21, 33), [24])), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K']],
    ],

    // ═══════════════════════════════════════════════════════════
    // A330-200 Layouts
    // ═══════════════════════════════════════════════════════════
    'a330-200a' => [
        ['name' => 'Economy Class - Rows 21-38', 'rows' => array_values(array_diff(range(21, 38), [24])), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K']],
        ['name' => 'Economy Class - Rows 39-52', 'rows' => range(39, 52), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K']],
    ],
    'a330-200b' => [
        ['name' => 'Economy Class - Rows 21-31', 'rows' => array_values(array_diff(range(21, 31), [24])), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K']],
        ['name' => 'Economy Class - Rows 32-45', 'rows' => range(32, 45), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K']],
    ],

    // ═══════════════════════════════════════════════════════════
    // A330-900 Layouts
    // ═══════════════════════════════════════════════════════════
    'a330-900a' => [
        ['name' => 'Economy Class - Rows 21-40', 'rows' => array_values(array_diff(range(21, 40), [24])), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K']],
        ['name' => 'Economy Class - Rows 41-58', 'rows' => range(41, 58), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K'], 'exceptions' => ['41A', '41C', '41H', '41K']],
    ],
    'a330-900b' => [
        ['name' => 'Economy Class - Rows 21-40', 'rows' => array_values(array_diff(range(21, 40), [24])), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K']],
        ['name' => 'Economy Class - Rows 41-58', 'rows' => range(41, 58), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K']],
    ],

    // ═══════════════════════════════════════════════════════════
    // A320 Layout (Single section, 3-3: A B C - D E F)
    // ═══════════════════════════════════════════════════════════
    'a320a' => [
        ['name' => 'Economy Class - Rows 1-31', 'rows' => array_values(array_diff(range(1, 31), [13])), 'columns' => ['A', 'B', 'C', 'D', 'E', 'F']],
    ],

    // ═══════════════════════════════════════════════════════════
    // ATR72 Layout (Single section, 2-2: A C - H K)
    // ═══════════════════════════════════════════════════════════
    'atr72' => [
        ['name' => 'Economy Class - Rows 1-19', 'rows' => array_values(array_diff(range(1, 19), [13])), 'columns' => ['A', 'B', 'C', 'D']],
    ],
];
