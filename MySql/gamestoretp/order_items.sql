create table gamestoretp.order_items
(
    id       integer serial
        primary key,
    order_id integer            not null,
    game_id  integer            not null,
    quantity integer            not null,
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

