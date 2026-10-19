<?php
/**
 * "น้องกล้า" mascot as inline SVG.
 *   mascot('wave', 'w-64')  — waving, eyes follow the mouse (class js-pupil, see app.js)
 *   mascot('hero', 'w-56')  — superhero version with a cape and mask
 */
function mascot(string $variant = 'wave', string $class = 'w-64', string $label = 'น้องกล้า มาสคอต Green Office'): void
{
    $hero = $variant === 'hero';
    $stroke = $hero ? ' stroke="#10302F" stroke-width="3"' : '';
    ?>
<svg role="img" aria-label="<?= e($label) ?>" class="mascot <?= e($class) ?>" viewBox="0 0 240 260" style="overflow:visible">
  <ellipse cx="120" cy="248" rx="70" ry="10" fill="#003D3C" opacity=".3"/>
  <?php if ($hero): ?>
  <path d="M58 104 Q8 196 30 252 L120 228 L210 252 Q232 196 182 104z" fill="#E2725B" stroke="#10302F" stroke-width="4" stroke-linejoin="round"/>
  <?php endif; ?>
  <path d="M120 70 C120 50 118 38 112 26" stroke="#2E7D32" stroke-width="6" fill="none" stroke-linecap="round"/>
  <path d="M112 30 C84 8 52 18 46 40 C72 48 98 46 112 30z" fill="<?= $hero ? '#8FBF3A' : '#C5E063' ?>"<?= $stroke ?>/>
  <path d="M114 32 C134 2 176 4 188 26 C160 44 132 44 114 32z" fill="<?= $hero ? '#6FA82E' : '#9CCB3B' ?>"<?= $stroke ?>/>
  <rect x="44" y="64" width="152" height="170" rx="76" fill="#E9F5EC"<?= $hero ? ' stroke="#10302F" stroke-width="4"' : '' ?>/>
  <?php if ($hero): ?>
  <path d="M70 116 Q92 104 112 116 L112 132 Q92 140 70 132z M128 116 Q148 104 170 116 L170 132 Q148 140 128 132z" fill="#10302F"/>
  <path d="M112 120 L128 120" stroke="#10302F" stroke-width="4"/>
  <circle class="js-pupil" data-cx="92" data-cy="124" cx="92" cy="124" r="4" fill="#ffffff"/>
  <circle class="js-pupil" data-cx="148" data-cy="124" cx="148" cy="124" r="4" fill="#ffffff"/>
  <circle cx="120" cy="198" r="16" fill="#FFC845" stroke="#10302F" stroke-width="3"/>
  <path d="M113 199 l5 5 9-10" stroke="#10302F" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
  <path class="arm" d="M190 140 Q222 112 222 78" stroke="#E9F5EC" stroke-width="18" fill="none" stroke-linecap="round"/>
  <circle cx="222" cy="72" r="13" fill="#E9F5EC" stroke="#10302F" stroke-width="3"/>
  <?php else: ?>
  <path d="M44 170 Q120 196 196 170 L196 200 Q196 234 120 234 Q44 234 44 200z" fill="#CFE8E4"/>
  <path d="M60 176 Q120 200 180 176 L176 194 Q120 214 64 194z" fill="#FFC845"/>
  <circle cx="92" cy="128" r="17" fill="#ffffff"/><circle cx="148" cy="128" r="17" fill="#ffffff"/>
  <circle class="js-pupil" data-cx="92" data-cy="130" cx="92" cy="130" r="8" fill="#10302F"/>
  <circle class="js-pupil" data-cx="148" data-cy="130" cx="148" cy="130" r="8" fill="#10302F"/>
  <path d="M78 104 Q92 96 106 102" stroke="#10302F" stroke-width="4" fill="none" stroke-linecap="round"/>
  <path d="M134 102 Q148 96 162 104" stroke="#10302F" stroke-width="4" fill="none" stroke-linecap="round"/>
  <path class="arm" d="M190 140 Q222 120 226 92" stroke="#E9F5EC" stroke-width="16" fill="none" stroke-linecap="round"/>
  <?php endif; ?>
  <circle cx="72" cy="154" r="9" fill="#FF9E80" opacity=".55"/><circle cx="168" cy="154" r="9" fill="#FF9E80" opacity=".55"/>
  <path d="<?= $hero ? 'M100 160 Q120 180 140 160' : 'M106 152 Q120 166 134 152' ?>" stroke="#10302F" stroke-width="<?= $hero ? 5 : 4 ?>" fill="none" stroke-linecap="round"/>
  <path d="M50 150 Q26 170 34 196" stroke="#E9F5EC" stroke-width="16" fill="none" stroke-linecap="round"/>
</svg>
    <?php
}

/** Simple leaf icon used in many places. */
function leaf_icon(string $class = 'w-6 h-6'): string
{
    return '<svg aria-hidden="true" class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 19c0-8 5-14 15-15-1 10-7 15-15 15z"/><path d="M5 19 13 11"/></svg>';
}

/** Icons for the 5 resources. */
function resource_icon(string $key, string $class = 'w-7 h-7'): string
{
    $paths = [
        'fuel'  => '<path d="M4 21V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v16"/><path d="M3 21h12M6 8h6"/><path d="M14 10h2a2 2 0 0 1 2 2v4a1.5 1.5 0 0 0 3 0V8l-3-3"/>',
        'ghg'   => '<path d="M7 18a4 4 0 0 1-.5-7.97A6 6 0 0 1 18 9a4.5 4.5 0 0 1-.5 9z"/><path d="M9 14.5h6"/>',
        'elec'  => '<path d="M13 2 4 14h7l-1 8 9-12h-7z"/>',
        'water' => '<path d="M12 2.7s6 6.4 6 11.3a6 6 0 0 1-12 0c0-4.9 6-11.3 6-11.3z"/>',
        'paper' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/>',
    ];
    return '<svg aria-hidden="true" class="ico-wave ' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . ($paths[$key] ?? '') . '</svg>';
}

function clip_icon(string $class = 'w-4 h-4'): string
{
    return '<svg aria-hidden="true" class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5 12.5 20a5 5 0 0 1-7-7L14 4.5a3.3 3.3 0 0 1 4.7 4.7l-8.5 8.5a1.7 1.7 0 0 1-2.4-2.4L15.5 7.5"/></svg>';
}
