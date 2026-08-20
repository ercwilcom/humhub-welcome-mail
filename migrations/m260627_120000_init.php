<?php

use humhub\components\Migration;

class m260627_120000_init extends Migration
{
    public function safeUp()
    {
        $this->createTable('welcome_mail_token', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            // sha256 hex of the raw token handed out in the email; the raw value is never stored.
            'token_hash' => $this->char(64)->notNull()->unique(),
            'created_at' => $this->dateTime()->notNull(),
            'expires_at' => $this->dateTime()->notNull(),
            'used_at' => $this->dateTime()->null(),
        ]);
        $this->createIndex('idx_welcome_token_user', 'welcome_mail_token', 'user_id');
        $this->createIndex('idx_welcome_token_expires', 'welcome_mail_token', 'expires_at');

        // Best-effort FK; ignored on engines/setups that don't support it.
        try {
            $this->addForeignKey(
                'fk_welcome_token_user',
                'welcome_mail_token',
                'user_id',
                'user',
                'id',
                'CASCADE',
                'CASCADE'
            );
        } catch (\Throwable $e) {
            // Non-fatal: the index above is enough for correctness.
        }
    }

    public function safeDown()
    {
        $this->dropTable('welcome_mail_token');
    }
}
