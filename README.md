SQL код создания базы
------------

Папка migrations

SQL запрос
------------

SET @last_url = '', @last_month='', @position = 0;
SELECT month as 'Месяц', url as 'Ссылка', countRedir as 'Кол-во переходов', topPos as 'Позиция в топе месяца по переходам' FROM (
    SELECT CONCAT( YEAR(h.datetime), CONCAT('-', MONTH(h.datetime) )) as month, l.url as url, COUNT(h.id) as countRedir, IF(@last_month = CONCAT( YEAR(h.datetime), CONCAT('-', MONTH(h.datetime) )), @position:=@position+1, @position:=1) as topPos, @last_month := CONCAT( YEAR(h.datetime), CONCAT('-', MONTH(h.datetime) ))
    FROM hit h
    LEFT JOIN link l ON h.link_id = l.id
    GROUP BY YEAR(h.datetime), MONTH(h.datetime), l.url
    ORDER BY 'Месяц' DESC, 'Кол-во переходов' DESC
) as T;