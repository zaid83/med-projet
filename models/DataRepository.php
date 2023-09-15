<?php


namespace models;

require 'vendor/autoload.php';

use DateTime;
use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;


class DataRepository
{

    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = \config\Database::getpdo();
    }

    public function validationPrelevement($data)
    {
        $requiredFields = ['sample', 'sea', 'date', 'startTime', 'startLatitude', 'startLongitude'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                echo "Champ '$field' manquant pour le prélèvement !";
                return false;
            }
        }

        return true;
    }

    public function formulairePrelevement()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;



            if ($this->validationPrelevement($data)) {
                $data['km2'] = $data["boatSpeed"] * 1.852 * 0.3333 * (60 * 10 * 1e-5);
                $data['filteredVolume'] = ($data["startFlowMeter"] - $data["endFlowMeter"]) * 0.3 * 0.2 * 0.6;

                // Les champs du formulaire sont valides, vous pouvez effectuer les actions nécessaires
                $data['concentration_km2'] = $data["particlesNumber"] / $data["km2"];
                $data['concentration_m3'] = $data["particlesNumber"] / $data["filteredVolume"];



                // Insérer les données dans la table "prelevements"
                $insert = $this->pdo->prepare("INSERT INTO prelevements (Sample,Campagne, Sea, Manta, Date,Trafic, cote_la_plus_proche, courant, Start_Time_UTC, End_Time_UTC, Start_Latitude, Start_Longitude, Mid_Latitude, Mid_Longitude, End_Latitude, End_Longitude, Boat_Speed_kt, Wind_Force_B, Wind_Speed_kt, Wind_Direction_deg, Sea_State_B, Temperature_C, pH, Oxygene_Dissous_mg_L,  	Salinite_SAL_PSU, Start_Flowmeter, End_Flowmeter, Volume_Filtered_m3, km2, Commentaires, Nombre_Particules_gt_1_mm, Concentration_nb_km2,Concentration_nb_m3) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

                $insert->execute(array($data["sample"], $data["campaign"], $data["sea"], $data["sample"], $data["date"], $data["trafic"], $data["nearCost"], $data["courant"], $data["startTime"], $data["endTime"], $data["startLatitude"], $data["startLongitude"], $data["midLatitude"], $data["midLongitude"], $data["endLatitude"], $data["endLongitude"], $data["boatSpeed"], $data["windForce"], $data["windSpeed"], $data["windDirection"], $data["seaState"], $data["waterTemperature"], $data["ph"], $data["oxygen"], $data["salinite"], $data["startFlowMeter"], $data["endFlowMeter"], $data["filteredVolume"], $data["km2"], $data["commentaires"], $data["particlesNumber"], $data['concentration_km2'], $data['concentration_m3']));

                // apres insertion ?

            }
        }
    }
    public function findAll()
    {
        $select = $this->pdo->prepare("SELECT * FROM prelevements ORDER by Date desc");
        $select->execute();

        return $select->fetchAll();
    }

    public function findAllBySample($id)
    {
        $select = $this->pdo->prepare("SELECT * FROM prelevements p join sea s on s.id_sea = p.sea  WHERE Sample = ?");
        $select->execute(array($id));

        return $select->fetch();
    }

    public function editBySample($id, $post)
    {
        $post['km2'] = $post["boatSpeed"] * 1.852 * 0.3333 * (60 * 10 * 1e-5);
        $post['filteredVolume'] = ($post["startFlowMeter"] - $post["endFlowMeter"]) * 0.3 * 0.2 * 0.6;

        $post['concentration_km2'] = $post["particlesNumber"] / $post["km2"];
        $post['concentration_m3'] = $post["particlesNumber"] / $post["filteredVolume"];

        $insert = $this->pdo->prepare("UPDATE prelevements SET Sample = ?, Campagne = ?, Sea = ?, Manta = ?, Date = ?, Trafic = ?, cote_la_plus_proche = ?, courant = ?, Start_Time_UTC = ?, End_Time_UTC = ?, Start_Latitude = ?, Start_Longitude = ?, Mid_Latitude = ?, Mid_Longitude = ?, End_Latitude = ?, End_Longitude = ?, Boat_Speed_kt = ?, Wind_force_B = ?, Wind_speed_kt = ?, Wind_direction_deg = ?, Sea_state_B = ?, Temperature_C = ?, pH = ?, Oxygene_dissous_mg_L = ?, Salinite_SAL_PSU = ?, Start_flowmeter = ?, End_flowmeter = ?, Volume_Filtered_m3 = ?, km2 = ?, Commentaires = ?, Nombre_Particules_gt_1_mm = ?, Concentration_nb_km2 = ?, Concentration_nb_m3 = ? WHERE Sample = ?");

        $insert->execute(array($post["sample"], $post["campaign"], $post["sea"], $post["sample"], $post["date"], $post["trafic"], $post["nearCost"], $post["courant"], $post["startTime"], $post["endTime"], $post["startLatitude"], $post["startLongitude"], $post["midLatitude"], $post["midLongitude"], $post["endLatitude"], $post["endLongitude"], $post["boatSpeed"], $post["windForce"], $post["windSpeed"], $post["windDirection"], $post["seaState"], $post["waterTemperature"], $post["ph"], $post["oxygen"], $post["salinite"], $post["startFlowMeter"], $post["endFlowMeter"], $post["filteredVolume"], $post["km2"], $post["commentaires"], $post["particlesNumber"], $post['concentration_km2'], $post['concentration_m3'], $id));
    }


    public function deleteBySample($id)
    {
        $delete = $this->pdo->prepare("DELETE FROM prelevements WHERE Sample = ?");
        $delete->execute(array($id));
    }
    public function findAllSample()
    {
        $select = $this->pdo->prepare("SELECT Sample FROM prelevements");
        $select->execute();

        return $select->fetchAll();
    }
    public function tableTri($post)
    {
        for ($i = 1; $i <= count($post) / 5; $i++) {
            $sous_tableau = array(
                "sample" => $post["sample_" . $i],
                "size" => $post["size_" . $i],
                "type" => $post["type_" . $i],
                "color" => $post["color_" . $i],
                "number" => $post["number_" . $i]
            );
            $tableau[] = $sous_tableau;
        }
        return $tableau;
    }

    public function triBySample($id)
    {
        $select = $this->pdo->prepare("SELECT id, Sample, sz.name as size, col.name as color, ty.name as type, Number
        FROM tri tr 
        join tri_type ty 
        on ty.id_type = tr.type
              join tri_color col 
              on col.id_color = tr.color
              join tri_size sz
             on sz.id_size = tr.size
         WHERE Sample = ?");
        $select->execute(array($id));

        return $select->fetchAll();
    }
    public function findAllTri()
    {
        $select = $this->pdo->prepare("SELECT *
        FROM tri tr 
        join tri_type ty 
        on ty.id_type = tr.type
              join tri_color col 
              on col.id_color = tr.color
              join tri_size sz
        on sz.id_size = tr.size");
        $select->execute();

        return $select->fetchAll();
    }


    public function findByTri($id)
    {
        $select = $this->pdo->prepare("SELECT * FROM tri WHERE id = ?");
        $select->execute(array($id));

        return $select->fetch();
    }

    public function editByTri($id, $post)
    {
        $insert = $this->pdo->prepare("UPDATE tri SET Sample = ?, Size = ?, Type = ?, Color = ?, Number = ? WHERE id = ?");
        $insert->execute(array($post["sample"], $post["size"], $post["type"], $post["color"], $post["number"], $id));
    }

    public function deleteByTri($id)
    {
        $delete = $this->pdo->prepare("DELETE FROM tri WHERE id = ?");
        $delete->execute(array($id));
    }
    public function addTri($sample, $size, $type, $color, $number)
    {
        $add = $this->pdo->prepare("INSERT INTO tri (Sample, Size, Type, Color, Number) VALUES (?,?,?,?,?)");
        $add->execute(array($sample, $size, $type, $color, $number));
    }
    public function numberBySample()
    {
        $select = $this->pdo->prepare('select SUM(Number) as "total", Sample FROM tri GROUP BY Sample');
        $select->execute();

        return $select->fetchAll();
    }
    public function findTypeBySample($id)
    {
        $select = $this->pdo->prepare('select Type, SUM(number) as "total" from tri where sample = ? group by Type');
        $select->execute(array($id));

        return $select->fetchAll();
    }
    public function findColorBySample($id)
    {
        $select = $this->pdo->prepare('select Color, SUM(number) as "total" from tri where sample = ? group by Color');
        $select->execute(array($id));

        return $select->fetchAll();
    }
    public function findSizeBySample($id)
    {
        $select = $this->pdo->prepare('select Size, SUM(number) as "total" from tri where sample = ? group by Size');
        $select->execute(array($id));

        return $select->fetchAll();
    }
    public function findDetailBySample($id)
    {
        $select = $this->pdo->prepare('SELECT Water_temperature, Filtered_volume, Commentaires, Sea_state, Start_time, Wind_force FROM prelevements WHERE Sample = ?');
        $select->execute(array($id));

        return $select->fetch();
    }

    public function findAllSize()
    {
        $select = $this->pdo->prepare("SELECT *
        FROM tri_size");
        $select->execute();

        return $select->fetchAll();
    }

    public function findAllType()
    {
        $select = $this->pdo->prepare("SELECT *
        FROM tri_type");
        $select->execute();

        return $select->fetchAll();
    }


    public function findAllColor()
    {
        $select = $this->pdo->prepare("SELECT *
        FROM tri_color");
        $select->execute();

        return $select->fetchAll();
    }




    public function importCSV($filename)
    {
        try {
            $spreadsheet = IOFactory::load($filename);
            $worksheet = $spreadsheet->getActiveSheet();

            $data = [];
            $firstRowSkipped = false; // Variable pour suivre si la première ligne a été sautée

            foreach ($worksheet->getRowIterator() as $row) {
                if (!$firstRowSkipped) {
                    $firstRowSkipped = true;
                    continue; // Sauter la première ligne
                }

                $rowData = [];
                foreach ($row->getCellIterator() as $cell) {
                    $rowData[] = $cell->getValue();
                }
                $data[] = $rowData;
            }

            // Renvoie le tableau des données extraites
            return $data;
        } catch (\Exception $e) {
            echo "<script type=\"text/javascript\">
                    alert(\"Error: {$e->getMessage()}\")
                  </script>";
            return []; // Retourne un tableau vide en cas d'erreur
        }
    }

    public function insertData($data)
    {

        $sample = $data[1];
        $size = $data[2];
        $type = $data[3];
        $color = $data[4];
        $number = $data[5];


        // Recherche du taille approprie
        $stmt = $this->pdo->prepare('SELECT id_size FROM tri_size WHERE name = ?');
        $stmt->execute([$size]);
        $resultsize = $stmt->fetch(PDO::FETCH_ASSOC);
        $sizeID = $resultsize['id_size'];

        // Recherche du type approprie
        $stmt = $this->pdo->prepare('SELECT id_type FROM tri_type WHERE name = ?');
        $stmt->execute([$type]);
        $resultType = $stmt->fetch(PDO::FETCH_ASSOC);
        $typeID = $resultType['id_type'];


        // Recherche de la couleur approprie
        $stmt = $this->pdo->prepare('SELECT id_color FROM tri_color WHERE name = ?');
        $stmt->execute([$color]);
        $resultColor = $stmt->fetch(PDO::FETCH_ASSOC);
        $colorID = $resultColor['id_color'];

        $insert = $this->pdo->prepare('INSERT INTO tri (Sample, Size, Type, Color, Number) VALUES (?, ?, ?, ?, ?)');
        $result = $insert->execute([$sample, $sizeID, $typeID, $colorID, $number]);

        if (!$result) {
            echo "<script type=\"text/javascript\">
                    alert(\"Database Error: Unable to Insert Data.\");
                  </script>";
        }
    }

    public function insertDataPrelevement($data)
    {/* 
        echo '<pre>';
        var_dump($data);
        echo '</pre>'; */
        $Echantillon = $data[0];
        $Campagne = $data[1];
        $Mer = $data[2];
        $Manta = $data[3];

        $dateAuFormatJJMMAAAA = $data[4];
        $dateTime = \DateTime::createFromFormat('d.m.Y', $dateAuFormatJJMMAAAA);
        if ($dateTime) {
            $Date = $dateTime->format('Y-m-d');
        }



        $Trafic = $data[5];
        $CoteLaPlusProche = $data[6];
        $Courant = $data[7];

        $heureTexte = $data[8];
        $timestamp = strtotime(str_replace('h', ':', $heureTexte));
        $Start_Time_UTC = date('H:i:s', $timestamp);


        $heureTexte = $data[9];
        $timestamp = strtotime(str_replace('h', ':', $heureTexte));
        $End_Time_UTC = date('H:i:s', $timestamp);


        $Start_Latitude = $data[10];
        $Start_Longitude = $data[11];
        $Mid_Latitude = $data[12];
        $Mid_Longitude = $data[13];
        $End_Latitude = $data[14];
        $End_Longitude = $data[15];
        $Boat_Speed_kt = $data[16];
        $Wind_Force_B = $data[17];
        $Wind_Speed_kt = $data[18];
        $Wind_Direction_deg = $data[19];
        $Sea_State_B = $data[20];
        $Temperature_C = $data[21];
        $pH = $data[22];
        $Oxygene_Dissous_mg_L = $data[23];
        $Salinite_SAL_PSU = $data[24];
        $Start_Flowmeter = $data[25];
        $End_Flowmeter = $data[26];
        $Volume_Filtered_m3 = ($data[26] - $data[25]) * 0.6 * 0.3 * 0.2;
        $Filtered_Distance = $data[28];
        $Volume_m3 = $data[29];


        $heureEnd = $End_Time_UTC;
        list($heures, $minutes, $secondes) = explode(':', $heureEnd);
        $End_time = ($heures * 60) + $minutes;

        $heureStart = $Start_Time_UTC;
        list($heures, $minutes, $secondes) = explode(':', $heureStart);
        $Start_time = ($heures * 60) + $minutes;

        $tt = $End_time - $Start_time;
        $km2 = $data[16] * 1.852 * ($tt / 60) * (60 * 1e-5);

        $Commentaires = $data[31];
        $Nombre_Particules_gt_1_mm = $data[32];
        var_dump($km2, $heureTexte);
        if ($km2 != 0) {
            $Concentration_nb_km2 = $data[32] / $km2;
        }
        $Concentration_nb_m3 = $data[32] / $Volume_Filtered_m3;

        $stmt = $this->pdo->prepare('SELECT id_sea FROM sea WHERE name = ?');
        $stmt->execute([$Mer]);
        $resultsea = $stmt->fetch(PDO::FETCH_ASSOC);
        $seaID = $resultsea['id_sea'];

        $insert = $this->pdo->prepare("INSERT INTO prelevements (Sample, Campagne, Sea, Manta, Date, Trafic, cote_la_plus_proche, courant, Start_Time_UTC, End_Time_UTC, Start_Latitude, Start_Longitude, Mid_Latitude, Mid_Longitude, End_Latitude, End_Longitude, Boat_Speed_kt, Wind_Force_B, Wind_Speed_kt, Wind_Direction_deg, Sea_State_B, Temperature_C, pH, Oxygene_Dissous_mg_L, Salinite_SAL_PSU, Start_Flowmeter, End_Flowmeter, Volume_Filtered_m3, Filtered_Distance, Volume_m3, km2, Commentaires, Nombre_Particules_gt_1_mm, Concentration_nb_km2, Concentration_nb_m3) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?)");
        $result = $insert->execute([$Echantillon, $Campagne, $seaID, $Manta, $Date, $Trafic, $CoteLaPlusProche, $Courant, $Start_Time_UTC, $End_Time_UTC, $Start_Latitude, $Start_Longitude, $Mid_Latitude, $Mid_Longitude, $End_Latitude, $End_Longitude, $Boat_Speed_kt, $Wind_Force_B, $Wind_Speed_kt, $Wind_Direction_deg, $Sea_State_B, $Temperature_C, $pH, $Oxygene_Dissous_mg_L, $Salinite_SAL_PSU, $Start_Flowmeter, $End_Flowmeter, $Volume_Filtered_m3, $Filtered_Distance, $Volume_m3, $km2, $Commentaires, $Nombre_Particules_gt_1_mm, $Concentration_nb_km2, $Concentration_nb_m3]);

        if (!$result) {
            echo "<script type=\"text/javascript\">
                    alert(\"Database Error: Impossible d'inserer les données.\");
                  </script>";
        }
    }

    public function importCSVSampling($filename)
    {
        try {
            $spreadsheet = IOFactory::load($filename);
            $worksheet = $spreadsheet->getActiveSheet();

            $data = [];
            $firstRowSkipped = false; // Variable pour suivre si la première ligne a été sautée

            foreach ($worksheet->getRowIterator() as $row) {
                if (!$firstRowSkipped) {
                    $firstRowSkipped = true;
                    continue; // Sauter la première ligne
                }

                // Stockez la ligne actuelle dans une variable temporaire
                $rowData = [];
                foreach ($row->getCellIterator() as $cell) {
                    $rowData[] = $cell->getValue();
                }

                // Vérifiez si la ligne actuelle n'est pas vide (c'est-à-dire, contient des données)
                $hasData = false;
                foreach ($rowData as $cellData) {
                    if (!empty($cellData)) {
                        $hasData = true;
                        break;
                    } else {
                        break;
                    }
                }

                // Si la ligne actuelle contient des données, ajoutez-la au tableau $data
                if ($hasData) {
                    $data[] = $rowData;
                }
            }

            // Renvoie le tableau des données extraites
            return $data;
        } catch (\Exception $e) {
            echo "<script type=\"text/javascript\">
                alert(\"Error: {$e->getMessage()}\")
              </script>";
            return []; // Retourne un tableau vide en cas d'erreur
        }
    }

    public function findSamplesByYear($year)
    {
        $select = $this->pdo->prepare("SELECT * FROM prelevements WHERE YEAR(Date) = ?");
        $select->execute(array($year));
        return $select->fetchAll();
    }

    public function findUniqueYears()
    {
        $select = $this->pdo->query("SELECT DISTINCT YEAR(Date) AS Year FROM prelevements ");
        return $select->fetchAll(PDO::FETCH_COLUMN);
    }

    public function findAllSeas()
    {
        $select = $this->pdo->prepare("SELECT id_sea, name from sea");
        $select->execute();
        return $select->fetchAll();
    }
    public function findTypeByTri()
    {
        $select = $this->pdo->prepare("SELECT DISTINCT Type from tri");
        $select->execute();
        return $select->fetchAll();
    }

    public function findSizeByTri()
    {
        $select = $this->pdo->prepare("SELECT DISTINCT Size from tri");
        $select->execute();
        return $select->fetchAll();
    }
    public function findColorByTri()
    {
        $select = $this->pdo->prepare("SELECT DISTINCT Color from tri");
        $select->execute();
        return $select->fetchAll();
    }
}
