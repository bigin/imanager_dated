DROP TABLE IF EXISTS "access";
CREATE TABLE "access" ("id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL , "target" VARCHAR NOT NULL , "principal_class" VARCHAR NOT NULL  DEFAULT gsPrincipal, "principal" INTEGER NOT NULL  DEFAULT 0, "autority" INTEGER NOT NULL  DEFAULT 9999, "policy" INTEGER NOT NULL  DEFAULT 0);
DROP TABLE IF EXISTS "settings";
CREATE TABLE "settings" ("key" VARCHAR NOT NULL , "value" TEXT NOT NULL , "xtype" VARCHAR NOT NULL  DEFAULT textfield, "namenspace" VARCHAR NOT NULL  DEFAULT core, "area" VARCHAR NOT NULL , "editedon" DATETIME NOT NULL  DEFAULT CURRENT_TIMESTAMP);
INSERT INTO "settings" VALUES('plugin_title','Account Manager','textfield','core','','2013-08-17 15:55:11');
INSERT INTO "settings" VALUES('admin_tab_items','settings, fields, category, edit, view','textfield','core','','2013-08-22 06:11:51');
DROP TABLE IF EXISTS "templates";
CREATE TABLE "templates" ("id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL  UNIQUE , "title" VARCHAR NOT NULL  UNIQUE , "content" TEXT NOT NULL , "context" CHAR NOT NULL  DEFAULT frontend, "section" VARCHAR);
INSERT INTO "templates" VALUES(1,'header','<div id="am-header">
    <h3 class="floated">[[account-manager-title]]Account Manager</h3>  
    <div class="edit-nav clearfix">
        <a href="load.php?id=amanager&fields" [[fields]] >[[amanager/custom_fields]]</a>
        <a href="load.php?id=amanager&category" [[category]] >[[amanager/manage_category]]</a>
        <a href="load.php?id=amanager&edit" [[edit]] >[[amanager/add_new]]</a>
        <a href="load.php?id=amanager&view" [[view]] >[[amanager/view_all]]</a>
    </div>
</div>','backend',NULL);
INSERT INTO "templates" VALUES(2,'outputorder','[[header]]
[[msg]]
[[selector]]
[[content]]','backend',NULL);
DROP TABLE IF EXISTS "users";
CREATE TABLE "users" ("id" INTEGER PRIMARY KEY NOT NULL , "username" VARCHAR UNIQUE, "password" VARCHAR NOT NULL , "cachepwd" VARCHAR NOT NULL , "active" BOOL NOT NULL  DEFAULT 1, "class_key" VARCHAR NOT NULL  DEFAULT gsUser, "remote_key" VARCHAR, "remote_data" TEXT, "hash_class" VARCHAR NOT NULL , "salt" VARCHAR NOT NULL , "primary_group" INTEGER NOT NULL  check(typeof("primary_group") = 'integer') , "sassion_stale" TEXT, "sudo" BOOL NOT NULL );
