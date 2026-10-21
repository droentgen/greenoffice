<?php /** Full-size poster viewer for every [data-poster] button on the page (wired up in app.js). */ ?>
<dialog id="poster-lightbox" class="poster-lb" aria-label="โปสเตอร์ขนาดใหญ่">
  <figure class="flex flex-col items-center gap-3 m-0" data-lb-figure>
    <img src="" alt="" class="anim-pop max-h-[78vh] max-w-full w-auto rounded-2xl bg-white shadow-2xl object-contain" data-lb-img>
    <figcaption class="text-center text-white">
      <span class="block font-display text-lg" data-lb-title></span>
      <span class="text-sm text-white/70" data-lb-meta></span>
      <a href="#" target="_blank" rel="noopener" class="ml-2 text-sm text-lime underline" data-lb-open>เปิดไฟล์เต็ม</a>
    </figcaption>
  </figure>
  <button type="button" class="lb-btn top-3 right-3" data-lb-close aria-label="ปิด">✕</button>
  <button type="button" class="lb-btn left-2 sm:left-5 top-1/2 -translate-y-1/2 text-2xl" data-lb-prev aria-label="โปสเตอร์ก่อนหน้า">‹</button>
  <button type="button" class="lb-btn right-2 sm:right-5 top-1/2 -translate-y-1/2 text-2xl" data-lb-next aria-label="โปสเตอร์ถัดไป">›</button>
</dialog>
