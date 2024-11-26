<template>
    <div class="produit-card">
      <img :src="produit.image" :alt="produit.nom" class="produit-image" />
      <h2>{{ produit.nom }}</h2>
      <p>{{ produit.description }}</p>
      <p><strong>Prix :</strong> {{ produit.prix.toFixed(2) }} €</p>
      <button @click="commander" class="btn">Commander</button>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      produit: {
        type: Object,
        required: true,
      },
    },
    methods: {
      commander() {
        alert(`Produit commandé : ${this.produit.nom}`);
      },
    },
  };
  </script>
  
  <style scoped>
  .produit-card {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 15px;
    width: 300px;
    text-align: center;
    background-color: #fff;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
  }
  
  .produit-image {
    max-width: 100%;
    height: auto;
    margin-bottom: 10px;
  }
  
  .btn {
    display: inline-block;
    padding: 10px 15px;
    background-color: #007bff;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    transition: background-color 0.3s ease;
  }
  
  .btn:hover {
    background-color: #0056b3;
  }
  </style>  