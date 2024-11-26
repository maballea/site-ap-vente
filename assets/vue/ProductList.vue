<template>
    <div class="produits-container">
      <ProductCard 
        v-for="produit in produits" 
        :key="produit.id" 
        :produit="produit" 
      />
    </div>
  </template>
  
  <script>
  import ProductCard from './ProductCard.vue';
  
  export default {
    components: {
      ProductCard,
    },
    data() {
      return {
        produits: [
          { id: 1, nom: 'Produit 1', description: 'Description du produit 1', prix: 10.99, image: '/path/to/image1.jpg' },
          { id: 2, nom: 'Produit 2', description: 'Description du produit 2', prix: 20.99, image: '/path/to/image2.jpg' },
          // Ajoutez d'autres produits ici
        ],
      };
    },
  };
  </script>
  
  <style scoped>
  .produits-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
  }
  </style>  