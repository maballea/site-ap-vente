<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241203082011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_6EEAA67D491EAAAE ON commande');
        $this->addSql('DROP INDEX IDX_6EEAA67D7EE5403C ON commande');
        $this->addSql('ALTER TABLE commande ADD reference VARCHAR(50) NOT NULL, ADD status VARCHAR(20) NOT NULL, ADD adresse_livraison VARCHAR(255) NOT NULL, ADD datelivraison_estimee DATETIME NOT NULL, DROP administrateur_id, DROP parcours_entrepot_id, CHANGE etat_commande date_creation VARCHAR(255) NOT NULL, CHANGE date_commande datecreation DATETIME NOT NULL, CHANGE total_commande total DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D19EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('DROP INDEX UNIQ_24CC0DF219EB6921 ON panier');
        $this->addSql('ALTER TABLE panier CHANGE client_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_24CC0DF2A76ED395 ON panier (user_id)');
        $this->addSql('ALTER TABLE produit DROP emplacement_entrepot, CHANGE description description LONGTEXT NOT NULL, CHANGE quantite_stock categorie_id INT NOT NULL');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        $this->addSql('CREATE INDEX IDX_29A5EC27BCF5E72D ON produit (categorie_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D19EB6921');
        $this->addSql('ALTER TABLE commande ADD administrateur_id INT DEFAULT NULL, ADD parcours_entrepot_id INT NOT NULL, ADD date_commande DATETIME NOT NULL, ADD etat_commande VARCHAR(255) NOT NULL, DROP reference, DROP date_creation, DROP datecreation, DROP status, DROP adresse_livraison, DROP datelivraison_estimee, CHANGE total total_commande DOUBLE PRECISION NOT NULL');
        $this->addSql('CREATE INDEX IDX_6EEAA67D491EAAAE ON commande (parcours_entrepot_id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67D7EE5403C ON commande (administrateur_id)');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27BCF5E72D');
        $this->addSql('DROP INDEX IDX_29A5EC27BCF5E72D ON produit');
        $this->addSql('ALTER TABLE produit ADD emplacement_entrepot VARCHAR(255) NOT NULL, CHANGE description description VARCHAR(255) NOT NULL, CHANGE categorie_id quantite_stock INT NOT NULL');
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF2A76ED395');
        $this->addSql('DROP INDEX UNIQ_24CC0DF2A76ED395 ON panier');
        $this->addSql('ALTER TABLE panier CHANGE user_id client_id INT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_24CC0DF219EB6921 ON panier (client_id)');
    }
}
