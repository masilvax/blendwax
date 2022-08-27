<?php
class DB{
    private static $_instance = null;
    private $_pdo,
    $_query,
    $_error = false,
    $_result,
    $_count = 0;
    
    
    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$_instance = new DB();
        }
        return self::$_instance;
    }
    
    public function __construct(){
        try{
            $this->_pdo = new PDO('mysql:host=localhost;dbname=14093285_blendwax;charset=utf8','14093285_blendwax','MaugoniaRozenek50');
            $this->_pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ); //ten parametr uaktywnia exceptiony, ale wyrzuca je znaim zrobi execute, więc w funkcji ponieżej nawet tam nie dojdzie
            $this->_pdo->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
            $this->query("SET NAMES 'utf8' COLLATE 'utf8_polish_ci'");//sprawdzic czy potrzebne
            $this->query("SET CHARACTER SET 'utf8'");//sprawdzic czy potrzebne
        }catch(PDOException $e){
            echo "TO TU";
            die($e->getMessage());
        }
    }
    
     public function query($sql, $params = array()){
        //var_dump($params);
        $this->_error = false;
        if($this->_query = $this->_pdo->prepare($sql)){
            $x=1;
            if(count($params)){
                foreach($params as $key=>$param){   //key po to, zeby moc w zaleznosci od niego odpowiednio
                                                    //zbindowac kolumne, np bindValue($x, $param, PDO::PARAM_LOB)
                    //echo $x.":".$param."<br/>";
                    $this->_query->bindValue($x,$param);//od 1
                    $x++;
                }
            }
            
            if($this->_query->execute()){
                if(strpos(strtolower($sql), 'select')!==false && strpos(strtolower($sql), 'insert')===false)
                    $this->_result = $this->_query->fetchAll(PDO::FETCH_ASSOC);//przy insertach.updejtach,deletach i ERRMODE_EXCEPTION nie ma co fetchowac i wywala exception, dlatego oifowałem (INSERT AS SELECT, wiec jak znajdzie select ale bez inserta tylko wtedy ma feczować)
                $this->_count = $this->_query->rowCount();
                //echo "<br/><br/><br/>ile: ".$this->_count."<br/><br/>";
            }else{
                
                //var_dump($this->_pdo->errorInfo());
                
                $this->_error = true;//moze throw exception?
                throw new Exception("błąd zapytania: ".$sql);
            }
        }
        return $this;
    }

    public function insert($table, $fields = array()){
        if(count($fields)){
            $keys = array_keys($fields);
            $values = '';
            $x=1;

            foreach($fields as $field){
                $values .= '?';
                if($x < count($fields)){
                    $values .= ', ';
                }
                $x++;
            }
        }
        $sql = "INSERT INTO $table (`".implode('`, `',$keys)."`) VALUES ($values)";
        //echo $sql;
        //var_dump($fields);
        if($this->query($sql,$fields)->error()){
            throw new Exception("Błąd inserta");//to itak sie nie wykona, bo sie wywala w query
        }else{
            return true;
        }

    }

    public function update($table, $id, $fields){
        $set = '';
        $x = 1;
        foreach($fields as $key => $val){
            $set .= "$key = ?";
            if($x < count($fields)){
                $set .= ', ';
            }
            $x++;
        }
        $sql = "UPDATE `$table` SET $set WHERE id = $id";
        //echo "::::".$sql.":::::";
        if($this->query($sql,$fields)->error()){
            throw new Exception("Błąd update'a");
        }else{
            return true;
        }
    }

    public function error(){
        return $this->_error;
    }

    public function getResult(){
        return $this->_result;
    }

    public function getRowCount(){
        return $this->_count;
    }

    public function getLastInsertId(){
        return $this->_pdo->lastInsertId();
    }

    //------------------------------------------- DAOs -----------
    
    
    //SEJWY-----------------------------------------
    public function saveOrUpdateArtysta($data){
        if(isset($_SESSION['user']))
            $data['kto_zm'] = $_SESSION['user']['login'];
        $data['data_zm'] = date("Y-m-d H:i:s");
        if($data['id']==="0"){
            //echo 'weszlo do inserta ';
            $this->insert("artysci", $data);
        }else{
            $id=$data['id'];
            unset($data['id']);
            $this->update("artysci",$id,$data);
        }
    }
    
    public function saveOrUpdateLabel($data){
        if(isset($_SESSION['user']))
            $data['kto_zm'] = $_SESSION['user']['login'];
        $data['data_zm'] = date("Y-m-d H:i:s");
        if($data['id']==="0"){
            //echo 'weszlo do inserta ';
            $this->insert("labele", $data);
        }else{
            $id=$data['id'];
            unset($data['id']);
            $this->update("labele",$id,$data);
        }
    }
    
    public function saveOrUpdateAlbum($data){
        if(isset($_SESSION['user']))
            $data['kto_zm'] = $_SESSION['user']['login'];
        $data['data_zm'] = date("Y-m-d H:i:s");
        if($data['id']==="0"){
            //echo 'weszlo do inserta ';
            $this->insert("albumy", $data);
        }else{
            $id=$data['id'];
            unset($data['id']);
            $this->update("albumy",$id,$data);
        }
    }
    
    public function saveOrUpdatePiosenka($data){
        if(isset($_SESSION['user']))
            $data['kto_zm'] = $_SESSION['user']['login'];
        $data['data_zm'] = date("Y-m-d H:i:s");
        if($data['id']==="0"){
            //echo 'weszlo do inserta ';
            $this->insert("piosenki", $data);
        }else{
            $id=$data['id'];
            unset($data['id']);
            $this->update("piosenki",$id,$data);
        }
    }
    
    public function saveOrUpdateBlend($data){
        //echo 'weszlem';
        if(isset($_SESSION['user']))
            $data['kto_zm'] = $_SESSION['user']['login'];
        $data['data_zm'] = date("Y-m-d H:i:s");
        if($data['id']==="0"){
            //echo 'weszlo do inserta ';
            $this->insert("blendy", $data);
        }else{
            $id=$data['id'];
            unset($data['id']);
            $this->update("blendy",$id,$data);
        }
    }
    public function saveOrUpdateSet($data){
        $data['data_zm'] = date("Y-m-d H:i:s");
        if($data['id']==="0"){
            //echo 'weszlo do inserta ';
            $data['data_dod'] = date("Y-m-d H:i:s");
            $this->insert("sety", $data);
        }else{
            $id=$data['id'];
            unset($data['id']);
            $this->update("sety",$id,$data);
        }
    }
    public function saveOrUpdateUzytkownik($data){
        if($data['id']==="0"){
            //echo 'weszlo do inserta ';
            $this->insert("uzytkownicy", $data);
        }else{
            $id=$data['id'];
            unset($data['id']);
            $this->update("uzytkownicy",$id,$data);
        }
    }
    
    public function saveOrUpdateLapkaS($data){
        if($data['id']==="0"){
            $data['data_dod'] = date("Y-m-d H:i:s");
            $this->insert("lapki_s", $data);
        }else{
            $id=$data['id'];
            unset($data['id']);
            $this->update("lapki_s",$id,$data);
        }
    }
    
    public function saveOrUpdateLapkaB($data){
        if($data['id']==="0"){
            $data['data_dod'] = date("Y-m-d H:i:s");
            $this->insert("lapki_b", $data);
        }else{
            $id=$data['id'];
            unset($data['id']);
            $this->update("lapki_b",$id,$data);
        }
    }
    
    public function dodajAlbumDoKolekcjiUzytkownika($idUzytkownika,$idAlbumu){
        $this->query("select * from kolekcje where id_uzytkownika=? and id_albumu=?",array($idUzytkownika,$idAlbumu));
        if($this->getRowCount()==0){
            $data = date("Y-m-d H:i:s");
            $this->query("insert into kolekcje (id_uzytkownika,id_albumu,data_dod) values (?,?,?)",array($idUzytkownika,$idAlbumu,$data));
        }
    }

    //USUWAJKI
    public function usunBlend($id){
        $this->query("delete from blendy where id=?",array($id));
        //$this->query("delete from lapki_b where id_blendu=?",array($id));
    }

    public function usunAlbumZKolekcjiUzytkownika($idUzytkownika,$idAlbumu){
        $this->query("delete from kolekcje where id_uzytkownika=? and id_albumu=?",array($idUzytkownika,$idAlbumu));
    }
    public function usunSet($id){
        $this->query("delete from sety where id=?",array($id));
        $this->query("delete from piosenki_w_setach where id_setu=?",array($id));
        //$this->query("delete from lapki_s where id_setu=?",array($id));
    }
    
    public function togglePubliczny($id){
        $this->query("update blendy set publiczny=not publiczny where id=?",array($id));
    }
    public function togglePublicznySet($id){
        $this->query("update sety set publiczny=not publiczny where id=?",array($id));
    }

    //POJEDYNCZE REKORDY ---------------------------------
    public function getLabelById($id){
        $this->query("select * from labele where id=?",array($id));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }

    public function getLabelByDiscogsId($id){
        $this->query("select * from labele where discogs_id=?",array($id));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function getLabelByNazwa($nazwa){
        $this->query("select * from labele where nazwa=?",array($nazwa));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function getArtystaById($id){
        $this->query("select * from artysci where id=?",array($id));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }

    public function getArtystaByDiscogsId($id){//pewnie nie uzywane, wiec mozna potem wywalic te funkcje
        $this->query("select * from artysci where discogs_id=?",array($id));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function getArtystaByNazwa($nazwa){
        $this->query("select * from artysci where nazwa=?",array($nazwa));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function getAlbumById($id){
        $this->query("select * from albumy where id=?",array($id));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }

    public function getAlbumByDiscogsId($id){
        $this->query("select * from albumy where discogs_id=?",array($id));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function getAlbumByNazwa($nazwa){
        $this->query("select * from albumy where nazwa=?",array($nazwa));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function getPiosenkaById($id){
        $this->query("select * from piosenki where id=?",array($id));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function getUzytkownikById($id){
        $this->query("select * from uzytkownicy where id=?",array($id));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    public function getUzytkownikByLogin($login){
        $this->query("select * from uzytkownicy where login=?",array($login));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }

    public function getPiosenkaByDiscogsId($id){//pewnie nie uzywane, wiec mozna potem wywalic te funkcje
        $this->query("select * from piosenki where discogs_id=?",array($id));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function getPiosenkaByNazwa($nazwa){
        $this->query("select * from piosenki where nazwa=?",array($nazwa));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function getLapkaS($idSetu,$idUzytkownika){
        $this->query("select * from lapki_s where id_setu=? and id_uzytkownika=?",array($idSetu,$idUzytkownika));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    public function getLapkaB($idSetu,$idUzytkownika){
        $this->query("select * from lapki_b where id_blendu=? and id_uzytkownika=?",array($idSetu,$idUzytkownika));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    //LISTY--------------------------
    public function mojaKolekcja($idUzytkownika,$limit,$offset,$sort){
        $this->query("select a.id, a.discogs_id, w.nazwa as artysta, a.tytul,a.rok,l.nazwa as label,a.nr_kat from kolekcje k, albumy a, artysci w, labele l where k.id_uzytkownika=? and k.id_albumu=a.id and a.id_artysty=w.id and a.id_labela=l.id order by ".$sort." limit ".$limit." offset ".$offset,array($idUzytkownika));
        if($this->getRowCount()>0){
            return $this->_result;
        }
        return false;
    }
    public function mojaKolekcjaSzukej($idUzytkownika,$limit,$offset,$sort,$szukaj){
        $szukaj = "%".$szukaj."%";
        $this->query("select a.id, a.discogs_id, w.nazwa as artysta, a.tytul,a.rok,l.nazwa as label,a.nr_kat from kolekcje k, albumy a, artysci w, labele l where k.id_uzytkownika=? and k.id_albumu=a.id and a.id_artysty=w.id and a.id_labela=l.id
        and (w.nazwa like ? or a.tytul like ? or l.nazwa like ?)
        order by ".$sort." limit ".$limit." offset ".$offset,array($idUzytkownika,$szukaj,$szukaj,$szukaj));
        if($this->getRowCount()>0){
            return $this->_result;
        }
        return false;
    }    
    public function mojaKolekcjaCount($idUzytkownika){
        $this->query("select count(*) as ile from kolekcje k, albumy a, artysci w, labele l where k.id_uzytkownika=? and k.id_albumu=a.id and a.id_artysty=w.id and a.id_labela=l.id",array($idUzytkownika));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    public function mojaKolekcjaCountSzukej($idUzytkownika,$szukaj){
        $szukaj = "%".$szukaj."%";
        $this->query("select count(*) as ile from kolekcje k, albumy a, artysci w, labele l where k.id_uzytkownika=? and k.id_albumu=a.id and a.id_artysty=w.id and a.id_labela=l.id
        and (w.nazwa like ? or a.tytul like ? or l.nazwa like ?)",array($idUzytkownika,$szukaj,$szukaj,$szukaj));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function albumUzytkownika($idAlbumu,$idUzytkownika){
        $this->query("select a.id, a.discogs_id, w.nazwa as artysta, a.tytul,a.rok,l.nazwa as label,a.nr_kat
                from kolekcje k, albumy a, artysci w, labele l where k.id_uzytkownika=? and k.id_albumu=a.id and a.id=? and a.id_artysty=w.id and a.id_labela=l.id order by w.nazwa, a.tytul",array($idUzytkownika,$idAlbumu));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function album($idAlbumu){
        $this->query("select a.id, a.discogs_id, w.nazwa as artysta, a.tytul,a.rok,l.nazwa as label,a.nr_kat
                from kolekcje k, albumy a, artysci w, labele l where k.id_albumu=a.id and a.id=? and a.id_artysty=w.id and a.id_labela=l.id order by w.nazwa, a.tytul",array($idAlbumu));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function piosenkiAlbumu($idAlbumu){
        $this->query("select * from piosenki where id_albumu=? order by pozycja",array($idAlbumu));
        if($this->getRowCount()>0){
            return $this->_result;
        }
        return false;
    }
    
    public function mojeBlendy($idUzytkownika,$limit,$offset,$sort){
        $this->query("select b.id, ar1.nazwa as artysta1, al1.tytul as album1, p1.tytul as piosenka1,b.opis,
                ar2.nazwa as artysta2, al2.tytul as album2, p2.tytul as piosenka2, p1.pozycja as pozycja1, p2.pozycja as pozycja2,b.publiczny
                from blendy b, albumy al1, albumy al2, artysci ar1, artysci ar2, piosenki p1, piosenki p2
                where b.id_uzytkownika=?
                and b.id_piosenki1=p1.id and b.id_piosenki2=p2.id
                and p1.id_albumu=al1.id and p2.id_albumu=al2.id
                and al1.id_artysty=ar1.id and al2.id_artysty=ar2.id
                order by ".$sort." limit ".$limit." offset ".$offset,array($idUzytkownika));
                //artysta1,album1,piosenka1,artysta2,album2,piosenka2"
        if($this->getRowCount()>0){
            return $this->_result;
        }
        return false;
    }
    
    public function mojeBlendySzukej($idUzytkownika,$limit,$offset,$sort,$szukaj){
        $szukaj = "%".$szukaj."%";
        $this->query("select b.id, ar1.nazwa as artysta1, al1.tytul as album1, p1.tytul as piosenka1,b.opis,
                ar2.nazwa as artysta2, al2.tytul as album2, p2.tytul as piosenka2, p1.pozycja as pozycja1, p2.pozycja as pozycja2,b.publiczny
                from blendy b, albumy al1, albumy al2, artysci ar1, artysci ar2, piosenki p1, piosenki p2
                where b.id_uzytkownika=?
                and b.id_piosenki1=p1.id and b.id_piosenki2=p2.id
                and p1.id_albumu=al1.id and p2.id_albumu=al2.id
                and al1.id_artysty=ar1.id and al2.id_artysty=ar2.id
                and (ar1.nazwa like ? or ar2.nazwa like ? or al1.tytul like ? or al2.tytul like ? or p1.tytul like ? or p2.tytul like ?)
                order by ".$sort." limit ".$limit." offset ".$offset,array($idUzytkownika,$szukaj,$szukaj,$szukaj,$szukaj,$szukaj,$szukaj));
                //artysta1,album1,piosenka1,artysta2,album2,piosenka2"
        if($this->getRowCount()>0){
            return $this->_result;
        }
        return false;
    }
    
    public function mojeBlendyCount($idUzytkownika){
        $this->query("select count(*) as ile
                from blendy b, albumy al1, albumy al2, artysci ar1, artysci ar2, piosenki p1, piosenki p2
                where b.id_uzytkownika=?
                and b.id_piosenki1=p1.id and b.id_piosenki2=p2.id
                and p1.id_albumu=al1.id and p2.id_albumu=al2.id
                and al1.id_artysty=ar1.id and al2.id_artysty=ar2.id",array($idUzytkownika));
                //artysta1,album1,piosenka1,artysta2,album2,piosenka2"
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function mojeBlendyCountSzukej($idUzytkownika,$szukaj){
        $szukaj = "%".$szukaj."%";
        $this->query("select count(*) as ile
                from blendy b, albumy al1, albumy al2, artysci ar1, artysci ar2, piosenki p1, piosenki p2
                where b.id_uzytkownika=?
                and b.id_piosenki1=p1.id and b.id_piosenki2=p2.id
                and p1.id_albumu=al1.id and p2.id_albumu=al2.id
                and al1.id_artysty=ar1.id and al2.id_artysty=ar2.id
                and (ar1.nazwa like ? or ar2.nazwa like ? or al1.tytul like ? or al2.tytul like ? or p1.tytul like ? or p2.tytul like ?)",array($idUzytkownika,$szukaj,$szukaj,$szukaj,$szukaj,$szukaj,$szukaj));
                //artysta1,album1,piosenka1,artysta2,album2,piosenka2"
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function blend($idBlendu,$idUzytkownika){
        $this->query("select b.id, b.id_piosenki1, b.id_piosenki2, ar1.nazwa as artysta1, al1.tytul as album1, al1.discogs_id as discogs_id1, p1.tytul as piosenka1,b.opis,
                ar2.nazwa as artysta2, al2.tytul as album2, al2.discogs_id as discogs_id2, p2.tytul as piosenka2, p1.pozycja as pozycja1, p2.pozycja as pozycja2,b.publiczny,
                (select count(*) from lapki_b where id_blendu=b.id and lapka='gora') as l_lapek_gora,
                (select count(*) from lapki_b where id_blendu=b.id and lapka='dol') as l_lapek_dol
                from blendy b, albumy al1, albumy al2, artysci ar1, artysci ar2, piosenki p1, piosenki p2
                where b.id_uzytkownika=? and b.id=?
                and b.id_piosenki1=p1.id and b.id_piosenki2=p2.id
                and p1.id_albumu=al1.id and p2.id_albumu=al2.id
                and al1.id_artysty=ar1.id and al2.id_artysty=ar2.id
                order by artysta1,album1,piosenka1,artysta2,album2,piosenka2",array($idUzytkownika,$idBlendu));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function mojeSety($idUzytkownika,$limit,$offset,$sort){
        $this->query("select s.*, date_format(s.data_dod,'%Y-%m-%d') as data_dodania,(select count(*) from piosenki_w_setach where id_setu=s.id) as liczba_piosenek,
        (select lapka from lapki_s where id_uzytkownika=? and id_setu=s.id limit 1) as lapka,
        (select count(*) from lapki_s where id_setu=s.id and lapka='gora') as l_lapek_gora,
        (select count(*) from lapki_s where id_setu=s.id and lapka='dol') as l_lapek_dol
        from sety s where s.id_uzytkownika=? order by ".$sort." limit ".$limit." offset ".$offset,array($idUzytkownika,$idUzytkownika));
        if($this->getRowCount()>0){
            return $this->_result;
        }
        return false;
    }
    
    public function mojeSetySzukej($idUzytkownika,$limit,$offset,$sort,$szukaj){
        $szukaj = "%".$szukaj."%";
        $this->query("select s.*, date_format(s.data_dod,'%Y-%m-%d') as data_dodania,(select count(*) from piosenki_w_setach where id_setu=s.id) as liczba_piosenek,
        (select lapka from lapki_s where id_uzytkownika=? and id_setu=s.id limit 1) as lapka,
        (select count(*) from lapki_s where id_setu=s.id and lapka='gora') as l_lapek_gora,
        (select count(*) from lapki_s where id_setu=s.id and lapka='dol') as l_lapek_dol
            from sety s where s.id_uzytkownika=?
            and (nazwa like ? or styl like ?)
            order by ".$sort." limit ".$limit." offset ".$offset,array($idUzytkownika,$idUzytkownika,$szukaj,$szukaj));
        if($this->getRowCount()>0){
            return $this->_result;
        }
        return false;
    }
    
    public function mojeSetyCount($idUzytkownika){
        $this->query("select count(*) as ile from sety where id_uzytkownika=?",array($idUzytkownika));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function mojeSetyCountSzukej($idUzytkownika,$szukaj){
        $szukaj = "%".$szukaj."%";
        $this->query("select count(*) as ile from sety
            where id_uzytkownika=? and (nazwa like ? or styl like ?)",array($idUzytkownika,$szukaj,$szukaj));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function blendPiosenki($idPiosenki,$idUzytkownika){//pasujace do ostatniej w secie
        $this->query("select p.id, ar.nazwa as artysta, al.tytul as album, al.discogs_id, p.pozycja, p.tytul as piosenka, b.opis
            from blendy b, piosenki p, albumy al, artysci ar, kolekcje k
            where 
            b.id_piosenki1=? and k.id_uzytkownika=?
            and b.id_piosenki2=p.id
            and k.id_albumu=al.id and al.id_artysty=ar.id and p.id_albumu=al.id
            order by artysta,album,pozycja,piosenka",array($idPiosenki,$idUzytkownika));
        if($this->getRowCount()>0){
            return $this->_result;
        }
        return false;
    }
    
    public function set($idSetu,$idUzytkownika){
        $this->query("select *,
                (select count(*) from lapki_s where id_setu=s.id and lapka='gora') as l_lapek_gora,
                (select count(*) from lapki_s where id_setu=s.id and lapka='dol') as l_lapek_dol
                from sety s where s.id=? and s.id_uzytkownika=?",array($idSetu,$idUzytkownika));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
        
    }

    public function setPiosenki($idSetu){//piosenki w secie
        $this->query("select ps.id as ps_id, ar.nazwa as artysta, a.tytul as album, p.tytul as piosenka, p.id , p.pozycja,ps.kolejnosc,a.discogs_id,ps.opis
                from piosenki_w_setach ps, piosenki p, albumy a, artysci ar
                where ps.id_piosenki=p.id and p.id_albumu=a.id and a.id_artysty=ar.id
                and id_setu=? order by ps.kolejnosc",array($idSetu));
        if($this->getRowCount()>0){
            return $this->_result;
        }
        return false;
    }
    
    /////cudzesy
    public function cudzeSety($idUzytkownika,$limit,$offset,$sort){
        $this->query("select s.*,u.login, date_format(s.data_dod,'%Y-%m-%d') as data_dodania,
        (select count(*) from piosenki_w_setach where id_setu=s.id) as liczba_piosenek,
        (select lapka from lapki_s where id_uzytkownika=? and id_setu=s.id limit 1) as lapka,
        (select count(*) from lapki_s where id_setu=s.id and lapka='gora') as l_lapek_gora,
        (select count(*) from lapki_s where id_setu=s.id and lapka='dol') as l_lapek_dol
        from sety s, uzytkownicy u
        where s.id_uzytkownika=u.id and s.publiczny='1' order by ".$sort." limit ".$limit." offset ".$offset,array($idUzytkownika));
        if($this->getRowCount()>0){
            return $this->_result;
        }
        return false;
    }
    
    public function cudzeSetySzukej($idUzytkownika,$limit,$offset,$sort,$szukaj){//idUzytkownika tylko dla łapek
        $szukaj = "%".$szukaj."%";
        $this->query("
        
        select s.*,u.login, date_format(s.data_dod,'%Y-%m-%d') as data_dodania,(select count(*) from piosenki_w_setach where id_setu=s.id) as liczba_piosenek,
        (select lapka from lapki_s where id_uzytkownika=? and id_setu=s.id limit 1) as lapka,
        (select count(*) from lapki_s where id_setu=s.id and lapka='gora') as l_lapek_gora,
        (select count(*) from lapki_s where id_setu=s.id and lapka='dol') as l_lapek_dol
            from sety s, uzytkownicy u
            where s.id_uzytkownika=u.id and s.publiczny='1'
            and (s.nazwa like ? or s.styl like ? or u.login like ?)
            order by ".$sort." limit ".$limit." offset ".$offset,array($idUzytkownika,$szukaj,$szukaj,$szukaj));
        if($this->getRowCount()>0){
            return $this->_result;
        }
        return false;
    }
    
    public function cudzeSetyCount(){
        $this->query("select count(*) as ile from sety where publiczny='1'");
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function cudzeSetyCountSzukej($szukaj){
        $szukaj = "%".$szukaj."%";
        $this->query("select count(*) as ile from sety s, uzytkownicy u
            where s.id_uzytkownika=u.id and s.publiczny='1' and (s.nazwa like ? or s.styl like ? or u.login like ?)",array($szukaj,$szukaj,$szukaj));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    //blendy
    public function cudzeBlendy($idUzytkownika,$limit,$offset,$sort){
        $this->query("select u.login, b.id, ar1.nazwa as artysta1, al1.tytul as album1, p1.tytul as piosenka1,b.opis,
                ar2.nazwa as artysta2, al2.tytul as album2, p2.tytul as piosenka2, p1.pozycja as pozycja1, p2.pozycja as pozycja2,b.publiczny,
                (select lapka from lapki_b where id_uzytkownika=? and id_blendu=b.id limit 1) as lapka,
                (select count(*) from lapki_b where id_blendu=b.id and lapka='gora') as l_lapek_gora,
                (select count(*) from lapki_b where id_blendu=b.id and lapka='dol') as l_lapek_dol
                from blendy b, albumy al1, albumy al2, artysci ar1, artysci ar2, piosenki p1, piosenki p2,uzytkownicy u
                where b.id_uzytkownika=u.id and b.publiczny='1'
                and b.id_piosenki1=p1.id and b.id_piosenki2=p2.id
                and p1.id_albumu=al1.id and p2.id_albumu=al2.id
                and al1.id_artysty=ar1.id and al2.id_artysty=ar2.id
                order by ".$sort." limit ".$limit." offset ".$offset,array($idUzytkownika));
                //artysta1,album1,piosenka1,artysta2,album2,piosenka2"
        if($this->getRowCount()>0){
            return $this->_result;
        }
        return false;
    }
    
    public function cudzeBlendySzukej($idUzytkownika,$limit,$offset,$sort,$szukaj){
        $szukaj = "%".$szukaj."%";
        $this->query("select u.login, b.id, ar1.nazwa as artysta1, al1.tytul as album1, p1.tytul as piosenka1,b.opis,
                ar2.nazwa as artysta2, al2.tytul as album2, p2.tytul as piosenka2, p1.pozycja as pozycja1, p2.pozycja as pozycja2,b.publiczny,
                (select lapka from lapki_b where id_uzytkownika=? and id_blendu=b.id limit 1) as lapka,
                (select count(*) from lapki_b where id_blendu=b.id and lapka='gora') as l_lapek_gora,
                (select count(*) from lapki_b where id_blendu=b.id and lapka='dol') as l_lapek_dol
                from blendy b, albumy al1, albumy al2, artysci ar1, artysci ar2, piosenki p1, piosenki p2,uzytkownicy u
                where b.id_uzytkownika=u.id and b.publiczny='1'
                and b.id_piosenki1=p1.id and b.id_piosenki2=p2.id
                and p1.id_albumu=al1.id and p2.id_albumu=al2.id
                and al1.id_artysty=ar1.id and al2.id_artysty=ar2.id
                and (ar1.nazwa like ? or ar2.nazwa like ? or al1.tytul like ? or al2.tytul like ? or p1.tytul like ? or p2.tytul like ? or u.login like ?)
                order by ".$sort." limit ".$limit." offset ".$offset,array($idUzytkownika,$szukaj,$szukaj,$szukaj,$szukaj,$szukaj,$szukaj,$szukaj));
                //artysta1,album1,piosenka1,artysta2,album2,piosenka2"
        if($this->getRowCount()>0){
            return $this->_result;
        }
        return false;
    }
    
    public function cudzeBlendyCount(){
        $this->query("select count(*) as ile
                from blendy b, albumy al1, albumy al2, artysci ar1, artysci ar2, piosenki p1, piosenki p2,uzytkownicy u
                where b.id_uzytkownika=u.id and b.publiczny='1'
                and b.id_piosenki1=p1.id and b.id_piosenki2=p2.id
                and p1.id_albumu=al1.id and p2.id_albumu=al2.id
                and al1.id_artysty=ar1.id and al2.id_artysty=ar2.id");
                //artysta1,album1,piosenka1,artysta2,album2,piosenka2"
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function cudzeBlendyCountSzukej($szukaj){
        $szukaj = "%".$szukaj."%";
        $this->query("select count(*) as ile
                from blendy b, albumy al1, albumy al2, artysci ar1, artysci ar2, piosenki p1, piosenki p2,uzytkownicy u
                where b.id_uzytkownika=u.id and b.publiczny='1'
                and b.id_piosenki1=p1.id and b.id_piosenki2=p2.id
                and p1.id_albumu=al1.id and p2.id_albumu=al2.id
                and al1.id_artysty=ar1.id and al2.id_artysty=ar2.id
                and (ar1.nazwa like ? or ar2.nazwa like ? or al1.tytul like ? or al2.tytul like ? or p1.tytul like ? or p2.tytul like ? or u.login like ?)",array($idUzytkownika,$szukaj,$szukaj,$szukaj,$szukaj,$szukaj,$szukaj,$szukaj));
                //artysta1,album1,piosenka1,artysta2,album2,piosenka2"
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function cudzyBlend($idBlendu,$idUzytkownika){
        $this->query("select b.id, b.id_piosenki1, b.id_piosenki2, ar1.nazwa as artysta1, al1.tytul as album1, al1.discogs_id as discogs_id1, p1.tytul as piosenka1,b.opis,
                ar2.nazwa as artysta2, al2.tytul as album2, al2.discogs_id as discogs_id2, p2.tytul as piosenka2, p1.pozycja as pozycja1, p2.pozycja as pozycja2,b.publiczny,u.login,
                (select lapka from lapki_b where id_uzytkownika=? and id_blendu=b.id limit 1) as lapka,
                (select count(*) from lapki_b where id_blendu=b.id and lapka='gora') as l_lapek_gora,
                (select count(*) from lapki_b where id_blendu=b.id and lapka='dol') as l_lapek_dol
                from blendy b, albumy al1, albumy al2, artysci ar1, artysci ar2, piosenki p1, piosenki p2,uzytkownicy u
                where b.id=?
                and b.id_uzytkownika=u.id
                and b.id_piosenki1=p1.id and b.id_piosenki2=p2.id
                and p1.id_albumu=al1.id and p2.id_albumu=al2.id
                and al1.id_artysty=ar1.id and al2.id_artysty=ar2.id
                order by artysta1,album1,piosenka1,artysta2,album2,piosenka2",array($idUzytkownika,$idBlendu));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
    public function cudzySet($idSetu,$idUzytkownika){
        $this->query("select s.*,u.login,
                (select lapka from lapki_s where id_uzytkownika=? and id_setu=s.id limit 1) as lapka,
                (select count(*) from lapki_s where id_setu=s.id and lapka='gora') as l_lapek_gora,
                (select count(*) from lapki_s where id_setu=s.id and lapka='dol') as l_lapek_dol
                from sety s,uzytkownicy u where s.id=? and s.id_uzytkownika=u.id",array($idUzytkownika,$idSetu));
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
        
    }
    
    ///////////cudzesy - koniec
    public function szukajPiosenek($szukaj,$idUzytkownika){
        $szukaj = "%".$szukaj."%";
        $this->query("select p.id, ar.nazwa as artysta, al.tytul as album, al.discogs_id, p.pozycja, p.tytul as piosenka from piosenki p, albumy al, artysci ar, kolekcje k where 
        k.id_uzytkownika=?
        and k.id_albumu=al.id and al.id_artysty=ar.id and p.id_albumu=al.id
        and (ar.nazwa like ? or al.tytul like ? or p.tytul like ?) order by artysta,album,pozycja,piosenka",array($idUzytkownika,$szukaj,$szukaj,$szukaj));
        if($this->getRowCount()>0){
            return $this->_result;
        }
        return false;
    }
    
    public function getWszystkieAlbumy(){//to chyba nigdzie nie jest wykorzystywane
        $this->query("select * from albumy");
        if($this->getRowCount()>0){
            return $this->_result[0];
        }
        return false;
    }
    
}

?>
