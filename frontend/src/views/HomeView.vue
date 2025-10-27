<script setup></script>

<template>
  <section class="hero">
    <div class="content">
      <h1>
        Services by Students,<br />
        <span>for Students</span>
      </h1>

      <p>
        ServIsko connects Miagao’s students and locals, making it easy to find, offer, and review
        trusted services in one convenient platform.
      </p>

      <div class="buttons">
        <Button label="Find Services" class="find" />
        <Button label="Offer Services" class="offer" outlined />
      </div>
    </div>

    <!-- Single smooth curved bottom -->
    <svg
      class="curve"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 1440 320"
      preserveAspectRatio="none"
    >
      <path fill="#ffffff" d="M0,0V7.23C0,65.52,268.63,112.77,600,112.77S1200,65.52,1200,7.23V0Z" />
    </svg>
  </section>
</template>

<style scoped>
.hero {
  position: relative;
  background-color: #6d0019;
  color: white;
  text-align: left;
  padding: 8rem 2rem 12rem;
  overflow: hidden;
}

.content {
  max-width: 800px;
  margin: 0 auto;
}

h1 {
  font-size: 4rem;
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 1rem;
}

h1 span {
  color: #f8dcdc;
}

p {
  color: #f3e9e9;
  font-size: 1.1rem;
  line-height: 1.6;
  max-width: 600px;
  margin-bottom: 2rem;
}

.buttons {
  display: flex;
  gap: 1rem;
}

.find {
  background-color: white;
  color: #6d0019;
  border: none;
  font-weight: 600;
}

.offer {
  border: 2px solid white;
  color: white;
  font-weight: 600;
}

/* Single curved bottom */
.curve {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 160px;
  display: block;
  pointer-events: none;
}
</style>
