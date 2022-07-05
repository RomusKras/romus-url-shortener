<?php

use yii\db\Migration;

/**
 * Class m220705_183720_initApp
 */
class m220705_183720_initApp extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        // Создание таблицы пользователей User
        $this->createTable('{{%user}}', [
            'id'                    => $this->primaryKey(),
            'username'              => $this->string(25)->notNull()->unique(),
            'auth_key'              => $this->string(32)->notNull(),
            'password_hash'         => $this->string()->notNull(),
            'password_reset_token'  => $this->string()->unique(),
            'email'                 => $this->string()->notNull()->unique(),
            'status'                => $this->smallInteger()->notNull()->defaultValue(10),
            'created'               => $this->dateTime()->notNull(),
            'modified'              => $this->dateTime(),
        ], $tableOptions);

        // Создание пользователя с данными demo/demo
        $user = new \app\models\User();
        $user->username = 'demo';
        $user->email = 'demo@demo.demo';
        $user->setPassword('demo');
        $user->generateAuthKey();
        $user->save(false);

        // Создание таблицы с ссылками Link
        $this->createTable('{{%link}}', [
            'id'            => $this->primaryKey(),
            'url'           => $this->string()->notNull(),
            'description'   => $this->string(255),
            'hash'          => $this->string()->notNull()->unique(),
            'counter'       => $this->integer()->notNull()->defaultValue(0),
            'created'       => $this->dateTime()->notNull()->comment('Дата создания'),
            'created_by'    => $this->integer(11)->notNull()->comment('Создавший пользователь')
        ], $tableOptions);
        $this->addForeignKey('fk_link_created_by', '{{%link}}', 'created_by', '{{%user}}', 'id', 'CASCADE', 'CASCADE');

        // Создание таблицы переходов по ссылкам Hit
        $this->createTable('{{%hit}}', [
            'id'                => $this->primaryKey(),
            'link_id'           => $this->integer(11)->notNull(),
            'datetime'          => $this->dateTime()->notNull(),
            'ip'                => $this->string(15)->notNull(),
            'country'           => $this->string(),
            'city'              => $this->string(),
            'user_agent'        => $this->string()->notNull(),
            'os'                => $this->string(),
            'os_version'        => $this->string(),
            'browser'           => $this->string(),
            'browser_version'   => $this->string(),
        ], $tableOptions);
        $this->addForeignKey('fk_hit_link_id', '{{%hit}}', 'link_id', '{{%link}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('idx_fk_hit_link_id', '{{%hit}}', 'link_id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%hit}}');
        $this->dropTable('{{%link}}');
        $this->dropTable('{{%user}}');
    }
}
