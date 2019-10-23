/*==============================================================*/
/* DBMS name:      MySQL 5.0                                    */
/* Created on:     23/10/2019 09.13.43                          */
/*==============================================================*/


drop table if exists fault_picture;

drop table if exists lamp_fault_report;

/*==============================================================*/
/* Table: fault_picture                                         */
/*==============================================================*/
create table fault_picture
(
   fault_picture_id     int not null,
   lamp_fault_report_id int not null,
   path_picture         varchar(1024),
   gps_info_exist       bool not null,
   gps_info             varchar(1024),
   primary key (fault_picture_id)
);

/*==============================================================*/
/* Table: lamp_fault_report                                     */
/*==============================================================*/
create table lamp_fault_report
(
   lamp_fault_report_id int not null,
   reporter_email       varchar(1024) not null,
   fault_street_address text not null,
   google_map_address   varchar(1024),
   fault_detail         text not null,
   report_date          datetime not null,
   follow_up            text,
   primary key (lamp_fault_report_id)
);

alter table fault_picture add constraint fk_lfr_fp foreign key (lamp_fault_report_id)
      references lamp_fault_report (lamp_fault_report_id) on delete restrict on update restrict;

