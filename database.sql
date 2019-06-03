CREATE DATABASE IF NOT EXISTS api_rest_laravel;

USE api_rest_laravel;

CREATE TABLE users(
    id  int(255) auto_increment NOT NULL,
    name    varchar(50) NOT NULL,
    surname varchar(50) NOT NULL,
    email   varchar(60) NOT NULL,
    password  varchar(255) NOT NULL,
    description text,
    role    varchar(20),
    image   varchar(255),
    created_at  datetime DEFAULT NULL,
    updated_at  datetime DEFAULT NULL,
    deleted_at  datetime DEFAULT NULL,
    remember_token  varchar(255),
    CONSTRAINT pk_users PRIMARY KEY(id)
)ENGINE=InnoDb;


create table categories(
    id int(255) auto_increment not null,
    name    varchar(100) not null,
    created_at  datetime default null,
    updated_at  datetime default null,
    deleted_at  datetime default null,
    constraint pk_categories primary key(id)
)ENGINE=InnoDb;

create table posts(
    id int(255) auto_increment not null,
    user_id int(255) not null,
    category_id int(255) not null,
    title   varchar(255) not null,
    content text not null,
    image   varchar(255),
    created_at  datetime default null,
    updated_at  datetime default null,
    deleted_at  datetime default null,
    constraint pk_posts primary key(id),
    constraint fk_post_user foreign key(user_id) references users(id),
    constraint fk_post_category foreign key(category_id) references categories(id)
)ENGINE=InnoDb;

