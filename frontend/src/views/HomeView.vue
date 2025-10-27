<script setup></script>

<template>
  <section class="hero">
    <div class="content">
      <h1>
        Services by Students,<br />
        for Students
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

    <svg
      class="curve"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 1440 320"
      preserveAspectRatio="none"
    >
      <path fill="#ffffff" d="M0,320 C480,160 960,160 1440,320 L1440,320 L0,320 Z" />
    </svg>
  </section>

  <section class="searchbar">
    <div class="search-content">
      <h2>Search & Browse Services</h2>
      <p>Find what you need quickly and easily.</p>

      <div class="search-box">
        <input type="text" placeholder="Search for services..." />
        <Button label="Search" class="search-btn" />
      </div>
    </div>
  </section>

  <section class="why-servisko">
    <div class="why-content">
      <h2>Why Choose ServIsko?</h2>
      <p>Connecting students and locals for trusted services.</p>
    </div>
    <div class="why-cards">
      <!-- Cards highlighting features can be added here -->
    </div>
  </section>

  <section class="how-servisko-works">
    <div class="work-card">
      <h2>How ServIsko Works</h2>
      <!--make two columns here-->
    </div>
  </section>

  <section class="featured-services">
    <h2>Featured Services</h2>
    <p>Lorem ipsum dolor sit amet, consectetur adipisicing</p>
    <div class="services-carousel">
      <!-- carousel of featured services added here -->
    </div>
  </section>

  <section class="service-categories">
    <h2>Popular Service Categories</h2>
    <p>Lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum</p>
    <div class="categories-cards">
      <!-- customized cards of service categories added here -->
    </div>
  </section>
</template>

<style scoped>
.hero {
  position: relative;
  background-color: #6d0019;
  color: white;
  text-align: left;
  padding: 8rem 2rem 8rem;
  overflow: hidden;
  height: 65vh;
  display: flex;
  align-items: center;
  justify-content: flex-start;
}

.content {
  max-width: 800px;
}

h1 {
  font-size: 4rem;
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 1rem;
  margin-top: -120px;
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

.curve {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 250px;
  display: block;
  pointer-events: none;
  z-index: 0;
}

.searchbar {
  background-color: #ffffff;
  text-align: center;
  padding: 5rem 2rem;
  margin-top: -100px;
  min-height: 35vh;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 3;
}

.searchbar .buttons {
  display: flex;
  gap: 1rem;
}

.searchbar .buttons button {
  flex: 1;
}

.search-content {
  max-width: 700px;
}

.search-content h2 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  color: #2a2a2a;
  margin-top: 6px;
}

.search-content p {
  color: #555;
  margin-bottom: 2rem;
}

.search-input {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
}

.search-input input {
  width: 60%;
  padding: 0.75rem 1rem;
  border: 2px solid #ccc;
  border-radius: 8px;
  font-size: 1rem;
}

.search-input input:focus {
  outline: none;
  border-color: #6d0019;
}

.why-servisko {
  background-color: #f9f9f9;
  text-align: center;
  padding: 5rem 2rem;
  width: 100%;
  height: 100vh;
}
.why-content {
  max-width: 700px;
  margin: 0 auto 3rem;
  text-align: center;
}
.why-content h2 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  color: #2a2a2a;
}

.why-content p {
  color: #555;
}

.how-servisko-works,
.featured-services,
.service-categories {
  width: 100%;
  height: 100vh;
}

.featured-services {
  background-color: #f0f0f0;
}
</style>
