<?php /* Shared <head>: fonts, Tailwind (CDN) theme and reusable component classes. */ ?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf" content="<?= e(csrf_token()) ?>">
<link rel="icon" href="<?= e(url('assets/img/logo.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Mitr:wght@400;500;600&family=Noto+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  // Brand colours and fonts — change them here and the whole site follows.
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          ku: '#006664', 'ku-deep': '#003D3C', 'ku-mid': '#0B7A77',
          mint: '#E6F3F1', lime: '#C5E063', leaf: '#9CCB3B', sun: '#FFC845',
          ink: '#10302F', cream: '#F8F6EC', muted: '#4A5F5D', line: '#E4E8DC'
        },
        fontFamily: {
          display: ['Mitr', 'sans-serif'],
          sans: ['"Noto Sans Thai"', 'system-ui', 'sans-serif']
        }
      }
    }
  };
</script>
<style type="text/tailwindcss">
  @layer components {
    .btn { @apply inline-flex items-center justify-center gap-2 min-h-[44px] px-5 py-2 rounded-full font-semibold transition duration-200 hover:-translate-y-0.5 active:translate-y-px disabled:opacity-50; }
    .btn-primary { @apply btn bg-ku text-white hover:shadow-lg hover:shadow-ku/30; }
    .btn-dark { @apply btn bg-ink text-white; }
    .btn-sun { @apply btn bg-sun text-ink; }
    .btn-outline { @apply btn border-2 border-ku text-ku bg-white; }
    .btn-sm { @apply min-h-[36px] px-3 py-1 text-sm; }
    .btn-danger { @apply btn border border-red-200 text-red-700 bg-white; }
    .card { @apply bg-white rounded-3xl border border-line; }
    .input { @apply w-full min-h-[46px] px-3 py-2 rounded-xl border-2 border-[#DDE3D9] bg-[#FAFBF8] font-normal text-base focus:border-ku focus:outline-none; }
    .label { @apply flex flex-col gap-1.5 font-semibold text-sm; }
    .chip { @apply inline-block text-xs font-semibold px-2.5 py-0.5 rounded-full whitespace-nowrap; }
    .eyebrow { @apply text-sm font-semibold text-ku tracking-wide; }
    .h-page { @apply font-display font-semibold text-[clamp(32px,5vw,52px)] leading-tight; }
  }
</style>
<link rel="stylesheet" href="<?= e(url('assets/css/app.css')) ?>">
