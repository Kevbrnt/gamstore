create table gamestoretp.order_items
(
    id       int auto_increment
        primary key,
    order_id int            not null,
    game_id  int            not null,
    quantity int            not null,
    price    decimal(10, 2) not null,
    constraint order_items_ibfk_1
        foreign key (order_id) references gamestoretp.orders (id)
            on delete cascade,
    constraint order_items_ibfk_2
        foreign key (game_id) references gamestoretp.games (id)
            on delete cascade
);

create index game_id
    on gamestoretp.order_items (game_id);

create index order_id
    on gamestoretp.order_items (order_id);

