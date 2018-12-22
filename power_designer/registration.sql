/*==============================================================*/
/* DBMS name:      MySQL 5.0                                    */
/* Created on:     21/12/2018 12.32.54                          */
/*==============================================================*/


drop table if exists CRUD_TABLE;

drop table if exists CR_PERMISSION;

drop table if exists DR_PERMISSION;

drop table if exists REGISTRAR;

drop table if exists RR_PERMISSION;

drop table if exists UR_PERMISSION;

drop table if exists USER_LOGIN;

drop table if exists USER_ROLE;

drop table if exists USER_ROLE_TYPE;

/*==============================================================*/
/* Table: CRUD_TABLE                                            */
/*==============================================================*/
create table CRUD_TABLE
(
   CRUD_TABLE_ID        int not null,
   NAME                 varchar(1024) not null,
   primary key (CRUD_TABLE_ID)
);

/*==============================================================*/
/* Table: CR_PERMISSION                                         */
/*==============================================================*/
create table CR_PERMISSION
(
   CR_PERMISSION_ID     int not null,
   USER_LOGIN_ID        int not null,
   CRUD_TABLE_ID        int not null,
   primary key (CR_PERMISSION_ID)
);

alter table CR_PERMISSION comment 'Create Record Permission';

/*==============================================================*/
/* Table: DR_PERMISSION                                         */
/*==============================================================*/
create table DR_PERMISSION
(
   DR_PERMISSION_ID     int not null,
   CRUD_TABLE_ID        int not null,
   USER_LOGIN_ID        int not null,
   RECORD_ID            int not null,
   primary key (DR_PERMISSION_ID)
);

alter table DR_PERMISSION comment 'Delete Record Permission';

/*==============================================================*/
/* Table: REGISTRAR                                             */
/*==============================================================*/
create table REGISTRAR
(
   REGISTRAR_ID         int not null,
   USER_LOGIN_ID        int not null,
   ROLE_NAME            varchar(255) not null,
   EMAIL                varchar(255) not null,
   MOBILE               varchar(255) not null,
   ADDRESS              text not null,
   PRODI_1              varchar(1024) not null,
   PRODI_2              varchar(1024) not null,
   primary key (REGISTRAR_ID)
);

/*==============================================================*/
/* Table: RR_PERMISSION                                         */
/*==============================================================*/
create table RR_PERMISSION
(
   RR_PERMISSION_ID     int not null,
   USER_LOGIN_ID        int not null,
   RECORD_ID            int not null,
   primary key (RR_PERMISSION_ID)
);

alter table RR_PERMISSION comment 'Read Record Permission';

/*==============================================================*/
/* Table: UR_PERMISSION                                         */
/*==============================================================*/
create table UR_PERMISSION
(
   UR_PERMISSION_ID     int not null,
   USER_LOGIN_ID        int not null,
   CRUD_TABLE_ID        int not null,
   RECORD_ID            int not null,
   primary key (UR_PERMISSION_ID)
);

alter table UR_PERMISSION comment 'Update Record Permission';

/*==============================================================*/
/* Table: USER_LOGIN                                            */
/*==============================================================*/
create table USER_LOGIN
(
   USER_LOGIN_ID        int not null,
   LOGIN                varchar(255) not null,
   PLAIN_PASSWORD       varchar(255) default NULL,
   HASHED_PASSWORD      varchar(255) default NULL,
   USER_LEVEL           int not null,
   primary key (USER_LOGIN_ID)
);

/*==============================================================*/
/* Table: USER_ROLE                                             */
/*==============================================================*/
create table USER_ROLE
(
   USER_ROLE_ID         int not null,
   USER_ROLE_TYPE_ID    int not null,
   USER_LOGIN_ID        int not null,
   primary key (USER_ROLE_ID)
);

/*==============================================================*/
/* Table: USER_ROLE_TYPE                                        */
/*==============================================================*/
create table USER_ROLE_TYPE
(
   USER_ROLE_TYPE_ID    int not null,
   ROLE_NAME            varchar(255) not null,
   primary key (USER_ROLE_TYPE_ID)
);

alter table CR_PERMISSION add constraint FK_RELATIONSHIP_4 foreign key (USER_LOGIN_ID)
      references USER_LOGIN (USER_LOGIN_ID) on delete restrict on update restrict;

alter table CR_PERMISSION add constraint FK_RELATIONSHIP_8 foreign key (CRUD_TABLE_ID)
      references CRUD_TABLE (CRUD_TABLE_ID) on delete restrict on update restrict;

alter table DR_PERMISSION add constraint FK_RELATIONSHIP_10 foreign key (CRUD_TABLE_ID)
      references CRUD_TABLE (CRUD_TABLE_ID) on delete restrict on update restrict;

alter table DR_PERMISSION add constraint FK_RELATIONSHIP_9 foreign key (USER_LOGIN_ID)
      references USER_LOGIN (USER_LOGIN_ID) on delete restrict on update restrict;

alter table REGISTRAR add constraint FK_FK_RELATIONSHIP_8 foreign key (USER_LOGIN_ID)
      references USER_LOGIN (USER_LOGIN_ID) on delete restrict on update restrict;

alter table RR_PERMISSION add constraint FK_RELATIONSHIP_5 foreign key (USER_LOGIN_ID)
      references USER_LOGIN (USER_LOGIN_ID) on delete restrict on update restrict;

alter table UR_PERMISSION add constraint FK_RELATIONSHIP_6 foreign key (USER_LOGIN_ID)
      references USER_LOGIN (USER_LOGIN_ID) on delete restrict on update restrict;

alter table UR_PERMISSION add constraint FK_RELATIONSHIP_7 foreign key (CRUD_TABLE_ID)
      references CRUD_TABLE (CRUD_TABLE_ID) on delete restrict on update restrict;

alter table USER_ROLE add constraint FK_FK_RELATIONSHIP_5 foreign key (USER_ROLE_TYPE_ID)
      references USER_ROLE_TYPE (USER_ROLE_TYPE_ID) on delete restrict on update restrict;

alter table USER_ROLE add constraint FK_RELATIONSHIP_3 foreign key (USER_LOGIN_ID)
      references USER_LOGIN (USER_LOGIN_ID) on delete restrict on update restrict;

