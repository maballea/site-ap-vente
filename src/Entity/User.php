<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string")
     */
    private $mdp;

    public function getId(): ?int
    {
        return $id;
    }

    public function getEmail(): ?string
    {
        return $email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getMdp(): ?string
    {
        return $mdp;
    }

    public function setMdp(string $mdp): self
    {
        $this->mdp = $mdp;

        return $this;
    }

    /**
     * Permet à un utilisateur de se connecter en vérifiant ses informations dans la base de données.
     */
    public function seConnecter(string $email, string $mdp, $entityManager): bool
    {
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => $email]);

        if ($user && password_verify($mdp, $user->getMdp())) {
            // L'utilisateur est connecté avec succès
            return true;
        }
        return false;
    }

    /**
     * Permet à un utilisateur de s'inscrire en ajoutant ses informations dans la base de données.
     */
    public function sInscrire($entityManager): bool
    {
        // Vérifier si l'utilisateur existe déjà
        $userRepository = $entityManager->getRepository(User::class);
        $existingUser = $userRepository->findOneBy(['email' => $this->getEmail()]);

        if ($existingUser) {
            // L'email est déjà utilisé
            return false;
        }

        // Hacher le mot de passe avant de l'enregistrer
        $this->setMdp(password_hash($this->getMdp(), PASSWORD_BCRYPT));

        // Enregistrer le nouvel utilisateur
        $entityManager->persist($this);
        $entityManager->flush();

        return true;
    }
}
