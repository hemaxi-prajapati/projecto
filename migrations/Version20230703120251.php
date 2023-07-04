<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230703120251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE daily_attendance (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, check_in TIME DEFAULT NULL, check_out TIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_9000F0F2A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE department (id INT AUTO_INCREMENT NOT NULL, teammanager_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_CD1DE18A82A78B99 (teammanager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE email_records (id INT AUTO_INCREMENT NOT NULL, to_email VARCHAR(255) NOT NULL, from_email VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meetings (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, subject VARCHAR(255) DEFAULT NULL, meeting_start_time DATETIME DEFAULT NULL, meeting_end_time DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_44FE52E2B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meetings_user (meetings_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_C8E9211F1EAF2177 (meetings_id), INDEX IDX_C8E9211FA76ED395 (user_id), PRIMARY KEY(meetings_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE otp_authentication (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, otp INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_4B6B6E45A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_assignment (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, user_id INT NOT NULL, assign_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_28633F88166D1F9C (project_id), INDEX IDX_28633F88A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_details (id INT AUTO_INCREMENT NOT NULL, project_manager_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, status VARCHAR(255) NOT NULL, attachment VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_E43807EF60984F51 (project_manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_report (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, project_id INT NOT NULL, status VARCHAR(255) NOT NULL, file_name VARCHAR(255) DEFAULT NULL, INDEX IDX_D38B9CCEA76ED395 (user_id), INDEX IDX_D38B9CCE166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task_with_project (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, priority VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, actual_start_date DATE NOT NULL, actual_end_date DATE NOT NULL, timer TIME DEFAULT NULL, progress INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_EECFCA20166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task_with_project_user (task_with_project_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_5289882B64C263DB (task_with_project_id), INDEX IDX_5289882BA76ED395 (user_id), PRIMARY KEY(task_with_project_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trasaction (id INT AUTO_INCREMENT NOT NULL, payment_id VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, amount INT NOT NULL, received_amount INT NOT NULL, created_timestamp INT NOT NULL, payment_method VARCHAR(255) NOT NULL, customer_id VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, department_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) DEFAULT NULL, firstname VARCHAR(255) NOT NULL, last_name VARCHAR(255) DEFAULT NULL, contact_number VARCHAR(20) DEFAULT NULL, exprience INT DEFAULT NULL, status VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, login_from VARCHAR(255) NOT NULL, is_deleted TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649AE80F5DF (department_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_profile_photo (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, source LONGTEXT NOT NULL, access_token LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_DEDB69AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE daily_attendance ADD CONSTRAINT FK_9000F0F2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A82A78B99 FOREIGN KEY (teammanager_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE meetings ADD CONSTRAINT FK_44FE52E2B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE meetings_user ADD CONSTRAINT FK_C8E9211F1EAF2177 FOREIGN KEY (meetings_id) REFERENCES meetings (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meetings_user ADD CONSTRAINT FK_C8E9211FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE otp_authentication ADD CONSTRAINT FK_4B6B6E45A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE project_assignment ADD CONSTRAINT FK_28633F88166D1F9C FOREIGN KEY (project_id) REFERENCES project_details (id)');
        $this->addSql('ALTER TABLE project_assignment ADD CONSTRAINT FK_28633F88A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE project_details ADD CONSTRAINT FK_E43807EF60984F51 FOREIGN KEY (project_manager_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE project_report ADD CONSTRAINT FK_D38B9CCEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE project_report ADD CONSTRAINT FK_D38B9CCE166D1F9C FOREIGN KEY (project_id) REFERENCES project_details (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE task_with_project ADD CONSTRAINT FK_EECFCA20166D1F9C FOREIGN KEY (project_id) REFERENCES project_details (id)');
        $this->addSql('ALTER TABLE task_with_project_user ADD CONSTRAINT FK_5289882B64C263DB FOREIGN KEY (task_with_project_id) REFERENCES task_with_project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_with_project_user ADD CONSTRAINT FK_5289882BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE user_profile_photo ADD CONSTRAINT FK_DEDB69AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daily_attendance DROP FOREIGN KEY FK_9000F0F2A76ED395');
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A82A78B99');
        $this->addSql('ALTER TABLE meetings DROP FOREIGN KEY FK_44FE52E2B03A8386');
        $this->addSql('ALTER TABLE meetings_user DROP FOREIGN KEY FK_C8E9211F1EAF2177');
        $this->addSql('ALTER TABLE meetings_user DROP FOREIGN KEY FK_C8E9211FA76ED395');
        $this->addSql('ALTER TABLE otp_authentication DROP FOREIGN KEY FK_4B6B6E45A76ED395');
        $this->addSql('ALTER TABLE project_assignment DROP FOREIGN KEY FK_28633F88166D1F9C');
        $this->addSql('ALTER TABLE project_assignment DROP FOREIGN KEY FK_28633F88A76ED395');
        $this->addSql('ALTER TABLE project_details DROP FOREIGN KEY FK_E43807EF60984F51');
        $this->addSql('ALTER TABLE project_report DROP FOREIGN KEY FK_D38B9CCEA76ED395');
        $this->addSql('ALTER TABLE project_report DROP FOREIGN KEY FK_D38B9CCE166D1F9C');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE task_with_project DROP FOREIGN KEY FK_EECFCA20166D1F9C');
        $this->addSql('ALTER TABLE task_with_project_user DROP FOREIGN KEY FK_5289882B64C263DB');
        $this->addSql('ALTER TABLE task_with_project_user DROP FOREIGN KEY FK_5289882BA76ED395');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649AE80F5DF');
        $this->addSql('ALTER TABLE user_profile_photo DROP FOREIGN KEY FK_DEDB69AA76ED395');
        $this->addSql('DROP TABLE daily_attendance');
        $this->addSql('DROP TABLE department');
        $this->addSql('DROP TABLE email_records');
        $this->addSql('DROP TABLE meetings');
        $this->addSql('DROP TABLE meetings_user');
        $this->addSql('DROP TABLE otp_authentication');
        $this->addSql('DROP TABLE project_assignment');
        $this->addSql('DROP TABLE project_details');
        $this->addSql('DROP TABLE project_report');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE task_with_project');
        $this->addSql('DROP TABLE task_with_project_user');
        $this->addSql('DROP TABLE trasaction');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_profile_photo');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
