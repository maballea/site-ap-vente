<template>
    <div id="app">
      <header>
        <h1>Bienvenue sur notre page d'accueil</h1>
      </header>
      <main>
        <ProductList />
      </main>
      <footer>
        <p>© 2024 Mon Application</p>
      </footer>
    </div>
  </template>
  
  <script>
  import ProductList from './ProductList.vue';
  
  export default {
    components: {
      ProductList,
    },
  };
  </script>
  
  <style scoped>
  header {
    text-align: center;
    background-color: #007bff;
    color: white;
    padding: 20px 0;
  }
  
  footer {
    text-align: center;
    margin-top: 20px;
  }
  </style>  