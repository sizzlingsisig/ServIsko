<script setup></script>

<template>
  <main>
    <div
      class="grid grid-cols-12 gap-4 grid-nogutter bg-surface-0 dark:bg-surface-950 text-surface-800 dark:text-surface-50"
    >
      <div class="col-span-12 md:col-span-6 p-12 text-center md:text-left flex items-center">
        <section>
          <span class="block text-6xl font-bold mb-1">Create the screens </span>
          <div class="text-6xl text-primary font-bold mb-4">your visitors deserve to see</div>
          <p class="mt-0 mb-6 text-surface-700 dark:text-surface-100 leading-normal">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor
            incididunt ut labore et dolore magna aliqua.
          </p>

          <Button label="Learn More" type="button" class="mr-4" raised></Button>
          <Button label="Live Demo" type="button" outlined></Button>
        </section>
      </div>
      <div class="col-span-12 md:col-span-6 overflow-hidden">
        <img
          src="https://cdn2.vectorstock.com/i/1000x1000/59/26/superhero-team-banners-set-vector-7135926.jpg"
          alt="Random Image"
          class="md:ml-auto block md:h-full"
          style="clip-path: polygon(8% 0, 100% 0%, 100% 100%, 0 100%)"
        />
      </div>
    </div>
  </main>
</template>
