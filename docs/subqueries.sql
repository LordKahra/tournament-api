// SELECT

SELECT *
FROM stores store
WHERE

(
    SELECT store_id
    FROM tournaments tournament
    WHERE field = '$value'
)

// Judged


WHERE id IN (
    SELECT store_id
    FROM tournaments
    WHERE head_judge = '$dci'
);

// Played



# Uploaded

SELECT store_id
FROM events
WHERE id in (
    SELECT event_id
    FROM tournaments
    WHERE id in (
        SELECT tournament_id
        FROM uploads upload
        WHERE upload.user_id = '$user_id'
    )

);

// Organizer
// Head Judge
// Uploader
// Player


SELECT *
FROM stores
WHERE id IN (
    SELECT store_id
    FROM events
    WHERE 
        user_id = '$user_id' OR
    id IN (
        SELECT event_id
        FROM tournaments
        WHERE
            organizer = '$dci' OR
            head_judge = '$dci' OR
            id IN (
                SELECT tournament_id
                FROM uploads upload
                WHERE user_id = '$user_id'
            ) OR
            id IN (
                SELECT tournament_id
                FROM rounds
                WHERE id IN (
                    SELECT round_id
                    FROM matches
                    WHERE id in (
                        SELECT match_id
                        FROM seats
                        WHERE dci = '$dci'
                    )
                )
            )
    )
)





