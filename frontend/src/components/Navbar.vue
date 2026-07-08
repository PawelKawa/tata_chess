<template>
  <nav class="navbar">
    <RouterLink to="/" class="navbar__brand">♟ Szachy Taty</RouterLink>
    <ul class="navbar__links">
      <li><RouterLink to="/">Aktualności</RouterLink></li>
      <li><RouterLink to="/o-mnie">O mnie</RouterLink></li>
      <li><RouterLink to="/kontakt">Kontakt</RouterLink></li>
    </ul>
  </nav>
</template>

<style scoped>
.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 2rem;
  background: #1a1a2e;
  color: white;
}
.navbar__brand {
  font-size: 1.4rem;
  font-weight: bold;
  color: white;
  text-decoration: none;
}
.navbar__links {
  display: flex;
  gap: 1.5rem;
  list-style: none;
  margin: 0;
  padding: 0;
}
.navbar__links a {
  color: #ccc;
  text-decoration: none;
}
.navbar__links a:hover,
.navbar__links a.router-link-active {
  color: white;
}
</style>
