<script setup>
import academicIcon from '@/assets/icons/academic-services-icon.jpg'
import designIcon from '@/assets/icons/design-and-creatives-icon.jpg'
import techIcon from '@/assets/icons/programming-tech-icon.jpg'
import writingIcon from '@/assets/icons/writing-icon.jpg'
import tutoringIcon from '@/assets/icons/tutoring-education-icon.jpg'

const cards = [
  {
    id: 1,
    path: 'M0.5 318.492V0.5H248.436V389.091L161.15 368.919L76.0054 345.189L0.5 318.492Z',
    title: 'Academic Services',
    subtitle: 'Serving Miagao',
    icon: academicIcon,
    x: 60,
    y: 150,
  },
  {
    id: 2,
    path: 'M277.889 393.243V0.5H511.366L516.186 417.567L470.668 414.601L372.672 404.515L277.889 393.243Z',
    title: 'Design and Creatives',
    subtitle: 'Offered daily',
    icon: designIcon,
    x: 320,
    y: 170,
  },
  {
    id: 3,
    path: 'M547.78 421.127L541.89 0.5H785.007L793.575 419.347L747.522 421.72L702.004 423.5H640.422H593.833L547.78 421.127Z',
    title: 'Programming & Tech',
    subtitle: 'By Iskolars',
    icon: techIcon,
    x: 610,
    y: 180,
  },
  {
    id: 4,
    path: 'M823.027 419.347L812.853 0.5H1044.72V400.363L1029.19 402.736L1004.03 406.295L974.038 409.262L950.476 411.041L908.172 414.601L869.616 417.567L823.027 419.347Z',
    title: 'Writing',
    subtitle: 'Real student feedback',
    icon: writingIcon,
    x: 870,
    y: 180,
  },
  {
    id: 5,
    path: 'M1068.82 396.803V0.5H1335.5V330.357L1319.44 335.696L1266.96 351.121L1223.05 362.987L1180.74 373.072L1137.37 383.751L1102.56 392.057L1068.82 396.803Z',
    title: 'Tutoring & Education',
    subtitle: 'Supporting livelihoods',
    icon: tutoringIcon,
    x: 1130,
    y: 170,
  },
]
</script>

<template>
  <section class="relative text-white flex justify-center items-center py-20">
    <svg
      viewBox="0 0 1336 424"
      class="w-full max-w-[1336px] h-auto"
      xmlns="http://www.w3.org/2000/svg"
    >
      <g v-for="card in cards" :key="card.id">
        <path :d="card.path" fill="#f3f3f3" stroke="black" />

        <foreignObject :x="card.x" :y="card.y" width="120" height="180">
          <div xmlns="http://www.w3.org/1999/xhtml" class="flex flex-col items-center">
            <img :src="card.icon" alt="" class="w-10 h-10 mb-3 object-contain" />
            <h3 class="text-lg font-bold text-[#6d0019] leading-tight">
              {{ card.title }}
            </h3>
            <p class="text-sm text-gray-600">{{ card.subtitle }}</p>
          </div>
        </foreignObject>
      </g>
    </svg>
  </section>
</template>
