<script setup>
import beautyImage from '@/assets/icons/4.png'
import designImage from '@/assets/icons/6.png'
import techImage from '@/assets/icons/5.png'
import writingImage from '@/assets/icons/3.png'
import tutoringImage from '@/assets/icons/2.png'

/*
<svg width="1336" height="423" viewBox="0 0 1336 423" fill="none" xmlns="http://www.w3.org/2000/svg">
<g opacity="0.8">
<path d="M0.5 318.492V0.5H254.5V194V381L161.5 362L76.5 342L0.5 318.492Z" fill="#F3F3F3"/>
<path d="M284.5 386V0.5H525.5V414.768L473 410.5L375 400.5L284.5 386Z" fill="#F3F3F3"/>
<path d="M560 417.567V0.5H794V421.72H747.522H702.5L641 421L594.5 419L560 417.567Z" fill="#F3F3F3"/>
<path d="M823.027 419.347V0.5H1071.5V400.5L1032.5 405L1004 408L975 411.041L949 413L908 415.5L869.616 417.567L823.027 419.347Z" fill="#F3F3F3"/>
<path d="M1097.5 397V0.5H1335.5V352.765L1323 355.5L1274 366.5L1227.5 376L1187.5 383L1142.5 390.5L1109 395.5L1097.5 397Z" fill="#F3F3F3"/>
<path d="M0.5 318.492V0.5H254.5V194V381L161.5 362L76.5 342L0.5 318.492Z" stroke="black"/>
<path d="M284.5 386V0.5H525.5V414.768L473 410.5L375 400.5L284.5 386Z" stroke="black"/>
<path d="M560 417.567V0.5H794V421.72H747.522H702.5L641 421L594.5 419L560 417.567Z" stroke="black"/>
<path d="M823.027 419.347V0.5H1071.5V400.5L1032.5 405L1004 408L975 411.041L949 413L908 415.5L869.616 417.567L823.027 419.347Z" stroke="black"/>
<path d="M1097.5 397V0.5H1335.5V352.765L1323 355.5L1274 366.5L1227.5 376L1187.5 383L1142.5 390.5L1109 395.5L1097.5 397Z" stroke="black"/>
</g>
</svg>

*/

const cards = [
  {
    id: 1,
    path: 'M0.5 318.492V0.5H254.5V194V381L161.5 362L76.5 342L0.5 318.492Z',
    title: 'Tutoring & Education',
    subtitle: 'Empower learning journeys',
    image: tutoringImage,
    x: 22,
    y: 40, // aligned top
  },
  {
    id: 2,
    path: 'M284.5 386V0.5H525.5V414.768L473 410.5L375 400.5L284.5 386Z',
    title: 'Writing',
    subtitle: 'Craft compelling content',
    image: writingImage,
    x: 298,
    y: 40, // aligned top
  },
  {
    id: 3,
    path: 'M560 417.567V0.5H794V421.72H747.522H702.5L641 421L594.5 419L560 417.567Z',
    title: 'Beauty and Cosmetics',
    subtitle: 'Enhance natural beauty',
    image: beautyImage,
    x: 573,
    y: 40, // aligned top
  },
  {
    id: 4,
    path: 'M823.027 419.347V0.5H1071.5V400.5L1032.5 405L1004 408L975 411.041L949 413L908 415.5L869.616 417.567L823.027 419.347Z',
    title: 'Programming and Tech',
    subtitle: 'Build innovative solutions',
    image: techImage,
    x: 843,
    y: 40, // aligned top
  },
  {
    id: 5,
    path: 'M1097.5 397V0.5H1335.5V352.765L1323 355.5L1274 366.5L1227.5 376L1187.5 383L1142.5 390.5L1109 395.5L1097.5 397Z',
    title: 'Design and Creatives',
    subtitle: 'Create stunnning visuals',
    image: designImage,
    x: 1108,
    y: 40, // aligned top
  },
]
</script>

<template>
  <section class="relative bg-white py-14">
    <!-- Header -->
    <div class="text-center mb-12">
      <h2 class="text-4xl font-bold text-gray-900 mb-3">Popular Listing Categories</h2>
      <p class="text-gray-600">Take a sneak peek at some of our categories</p>
    </div>

    <!-- SVG Cards Container -->
    <div class="flex justify-center items-center px-4">
      <svg
        viewBox="0 0 1336 450"
        class="w-full max-w-[1336px] h-auto"
        xmlns="http://www.w3.org/2000/svg"
      >
        <g v-for="card in cards" :key="card.id">
          <path :d="card.path" fill="white" stroke="#333" stroke-width="1.5" />

          <foreignObject :x="card.x" :y="card.y" width="210" height="330">
            <div xmlns="http://www.w3.org/1999/xhtml" class="flex flex-col items-center">
              <div class="w-48 h-60 mb-3 overflow-hidden">
                <img :src="card.image" alt="" class="w-full h-full object-cover" />
              </div>
              <h3 class="text-sm font-bold text-[#6d0019] text-center mb-1 leading-tight px-2">
                {{ card.title }}
              </h3>
              <p class="text-xs text-gray-600 text-center px-2">{{ card.subtitle }}</p>
            </div>
          </foreignObject>
        </g>
      </svg>
    </div>
  </section>
</template>
