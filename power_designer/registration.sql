/*==============================================================*/
/* DBMS name:      MySQL 5.0                                    */
/* Created on:     25/12/2018 16.02.10                          */
/*==============================================================*/


drop table if exists applicant;

drop table if exists crud_table;

drop table if exists cr_permission;

drop table if exists dr_permission;

drop table if exists rr_permission;

drop table if exists uri_permission;

drop table if exists ur_permission;

drop table if exists user_login;

drop table if exists user_role;

drop table if exists user_role_type;

/*==============================================================*/
/* Table: applicant                                             */
/*==============================================================*/
create table applicant
(
   applicant_id         int not null auto_increment,
   user_login_id        int not null,
   birth_date           date not null,
   birth_place          varchar(1024) not null,
   address              text not null,
   prodi_1              varchar(1024) not null,
   prodi_2              varchar(1024) not null,
   high_school          varchar(1024) not null,
   email_2              varchar(1024),
   phone_2              varchar(255),
   path_documents       varchar(1024),
   path_picture         varchar(1024),
   primary key (applicant_id)
);

/*==============================================================*/
/* Table: crud_table                                            */
/*==============================================================*/
create table crud_table
(
   crud_table_id        int not null,
   name                 varchar(1024) not null,
   primary key (crud_table_id)
);

/*==============================================================*/
/* Table: cr_permission                                         */
/*==============================================================*/
create table cr_permission
(
   cr_permission_id     int not null,
   user_login_id        int not null,
   crud_table_id        int not null,
   policy               bool not null comment '! ALLOW/ACCEPT, 0 REJECT/DENY',
   primary key (cr_permission_id)
);

alter table cr_permission comment 'Create Record Permission';

/*==============================================================*/
/* Table: dr_permission                                         */
/*==============================================================*/
create table dr_permission
(
   dr_permission_id     int not null,
   crud_table_id        int not null,
   user_login_id        int not null,
   record_id            int not null,
   policy               bool not null,
   primary key (dr_permission_id)
);

alter table dr_permission comment 'Delete Record Permission';

/*==============================================================*/
/* Table: rr_permission                                         */
/*==============================================================*/
create table rr_permission
(
   rr_permission_id     int not null,
   crud_table_id        int not null,
   user_login_id        int not null,
   record_id            int not null,
   policy               bool not null comment 'set 1 for ACCEPT/ALLOW, default 0 is DENIED/REJECT',
   primary key (rr_permission_id)
);

alter table rr_permission comment 'Read Record Permission';

/*==============================================================*/
/* Table: uri_permission                                        */
/*==============================================================*/
create table uri_permission
(
   uri_permission_id    int not null,
   user_login_id        int not null,
   uri                  varchar(1024) not null,
   policy               bool not null,
   primary key (uri_permission_id)
);

/*==============================================================*/
/* Table: ur_permission                                         */
/*==============================================================*/
create table ur_permission
(
   ur_permission_id     int not null,
   user_login_id        int not null,
   crud_table_id        int not null,
   record_id            int not null,
   policy               bool not null comment '! ALLOW/ACCEPT, 0 REJECT/DENY',
   primary key (ur_permission_id)
);

alter table ur_permission comment 'Update Record Permission';

/*==============================================================*/
/* Table: user_login                                            */
/*==============================================================*/
create table user_login
(
   user_login_id        int not null auto_increment,
   login                varchar(255) not null,
   plain_password       varchar(255) default null,
   hashed_password      varchar(255) not null ,
   user_level           int not null,
   email                varchar(255) not null,
   phone                varchar(32),
   primary key (user_login_id)
);

/*==============================================================*/
/* Table: user_role                                             */
/*==============================================================*/
create table user_role
(
   user_role_id         int not null,
   user_role_type_id    int not null,
   user_login_id        int not null,
   primary key (user_role_id)
);

/*==============================================================*/
/* Table: user_role_type                                        */
/*==============================================================*/
create table user_role_type
(
   user_role_type_id    int not null,
   role_name            varchar(255) not null,
   primary key (user_role_type_id)
);

alter table applicant add constraint fk_fk_relationship_8 foreign key (user_login_id)
      references user_login (user_login_id) on delete restrict on update restrict;

alter table cr_permission add constraint fk_relationship_4 foreign key (user_login_id)
      references user_login (user_login_id) on delete restrict on update restrict;

alter table cr_permission add constraint fk_relationship_8 foreign key (crud_table_id)
      references crud_table (crud_table_id) on delete restrict on update restrict;

alter table dr_permission add constraint fk_relationship_10 foreign key (crud_table_id)
      references crud_table (crud_table_id) on delete restrict on update restrict;

alter table dr_permission add constraint fk_relationship_9 foreign key (user_login_id)
      references user_login (user_login_id) on delete restrict on update restrict;

alter table rr_permission add constraint fk_relationship_11 foreign key (crud_table_id)
      references crud_table (crud_table_id) on delete restrict on update restrict;

alter table rr_permission add constraint fk_relationship_5 foreign key (user_login_id)
      references user_login (user_login_id) on delete restrict on update restrict;

alter table uri_permission add constraint fk_relationship_12 foreign key (user_login_id)
      references user_login (user_login_id) on delete restrict on update restrict;

alter table ur_permission add constraint fk_relationship_6 foreign key (user_login_id)
      references user_login (user_login_id) on delete restrict on update restrict;

alter table ur_permission add constraint fk_relationship_7 foreign key (crud_table_id)
      references crud_table (crud_table_id) on delete restrict on update restrict;

alter table user_role add constraint fk_fk_relationship_5 foreign key (user_role_type_id)
      references user_role_type (user_role_type_id) on delete restrict on update restrict;

alter table user_role add constraint fk_relationship_3 foreign key (user_login_id)
      references user_login (user_login_id) on delete restrict on update restrict;

