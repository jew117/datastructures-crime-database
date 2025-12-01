CREATE PROCEDURE GetCrimeStats(
    IN startDate DATE,
    IN endDate DATE
)
BEGIN
    SELECT crime_type, COUNT(*) AS total_incidents
    FROM incidents
    WHERE report_date BETWEEN startDate AND endDate
    GROUP BY crime_type
    ORDER BY total_incidents DESC;
END;


