import pandas as pd
from sqlalchemy import create_engine, text

MYSQL_USER = "root"
MYSQL_PASS = "root"  
MYSQL_DB   = "crime_analytics_akron"

engine = create_engine(f"mysql+pymysql://{MYSQL_USER}:{MYSQL_PASS}@localhost:3306/{MYSQL_DB}?charset=utf8mb4")

# 1) Read the CSV you just exported
csv_path = "akronpd_data.csv"   
df = pd.read_csv(csv_path)

# 2) Normalize column names expected by your table
df = df.rename(columns={
    "Agency":"department_code",
    "Report":"report_number",
    "Date":"incident_datetime",
    "Type":"crime_type",
    "Location":"location"
})

# 3) Basic cleaning
df["department_code"] = df["department_code"].str.upper().str.contains("APD").map({True:"APD", False:"UAPD"})
df["incident_datetime"] = pd.to_datetime(df["incident_datetime"], errors="coerce")
df["reported_datetime"] = df["incident_datetime"]
df["is_closed"] = False
df["resolution"] = None

# 4) Upsert
with engine.begin() as conn:
    for row in df.to_dict(orient="records"):
        conn.execute(text("""
            INSERT INTO incidents
            (department_code, report_number, incident_datetime, reported_datetime, crime_type, location, is_closed, resolution)
            VALUES (:department_code, :report_number, :incident_datetime, :reported_datetime, :crime_type, :location, :is_closed, :resolution)
            ON DUPLICATE KEY UPDATE
              incident_datetime = VALUES(incident_datetime),
              crime_type = VALUES(crime_type),
              location = VALUES(location),
              updated_at = CURRENT_TIMESTAMP;
        """), row)

print(f"Loaded {len(df)} rows.")