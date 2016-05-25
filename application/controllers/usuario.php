<?php
session_start();
class Usuario extends CI_Controller
{
	public function index(){
		$this->login();
	}

	public function foto(){
		$this->load->view('usuario/pruebaFoto');
	}
	
	public function fotoPost(){
		$nombre = $_FILES ['imagenPerfil']['name'];
		//console.log($nombre);
		$carpeta = "C://xampp/htdocs/ProyectoCalendario/assets/imagenes/perfil/";
		//copy ( $_FILES['imagenUsuario']['tmp_name'], $carpeta . $nombre );
		
		//echo "El fichero $nombre se almacen&oacute; en $carpeta";
		//return "<img src=".base_url()."assets/imagenes/perfil/".$nombre.">";
		mkdir(base_url()."assets/imagenes/perfil", 0777, true);
		move_uploaded_file($_FILES['imagenPerfil']['tmp_name'], $carpeta.$nombre);
		$imagen= "<img src=".base_url().'assets/imagenes/perfil/'.$nombre.">";
		echo $imagen;
		
	}
	
	public function fotoPerfil(){
		$nombre= $_REQUEST['nombreFoto'];
		$imagenEnviada= $_REQUEST['imagenUsuario'];
		$carpeta= base_url()."assets/imagenes/perfil/";
		
		//Damos permisos a la carpeta para que se pueda guardar la foto (si no da error)
		mkdir(base_url()."assets/imagenes/perfil", 0777, true);
		move_uploaded_file($_FILES[$imagenEnviada]['tmp_name'], $carpeta.$nombre);
		
		$imagen= "<img src=".base_url().'assets/imagenes/perfil/'.$nombre.">";
		echo $imagen;
	}
	
    /*----- Registrar usuarios -----*/
    public function registrar() {
        //$this->load->view ('templates/header');
        $datos['pantalla']= "registro";
        $this->load->view ('templates/headerSinCabecera', $datos);
        //$this->load->view ('usuario/formuRegistro');
        $this->load->view ('usuario/registro2');
        $this->load->view ('templates/footer3');
    }

    public function registrarPost() {
        $nick= $_REQUEST ['nick'];
        $password= $_REQUEST ['password'];
        $password2= $_REQUEST['password2'];
        $correo= $_REQUEST ['correo'];
        $clave= $_REQUEST ['clave'];
        $res= $_REQUEST ['res'];

        $rol= "";
        $longitudCorreo= strlen ($correo);

        $digitoRol= substr ($clave, -1); //cogemos el ultimo caracter de la clave, para saber el rol

        if($digitoRol==1){
            $rol= "profesor";
        }

        else{
            $rol= "alumno";
        }
        
        $this->load->model ('Model_Usuario', 'mu');
        $existeClave= $this->mu->comprobarClave($clave);
		
        if($existeClave!="") {
            $id= $existeClave;
            $_SESSION ['idUsuario']= $id;
            
            if($password == $password2){
            	if($res==true && $longitudCorreo<46) {
                	$this->mu->completarRegistro ($nick, $password, $correo, $rol, $clave, $id);
                	$this->login();
            	}
            }
            else{
            	$this->load->view('usuario/registro2');
            }
        }
        else {
            $this->load->view ('templates/headerSinCabecera');
            $this->load->view ('errors/noClave');
            $this->load->view ('templates/footer3');
        }

    }

    /*----- Login usuarios -----*/

    public function login() {
    	$datos['pantalla']= "login";
        //$this->load->helper ('form');
        //$this->load->view ('templates/header');
        $this->load->view ('templates/header2', $datos);
        $this->load->view ('usuario/login');
        $this->load->view ('templates/footer2');
        //$this->load->view ('templates/footer');
    }

    public function loginPost() {
        $nick= $_REQUEST ['user'];
        $password=$_REQUEST ['password'];
        $remember= isset ($_POST['remember']) ? TRUE : FALSE;

        $this->load->model('Model_Usuario', 'mu');
        $existeUsuario= $this->mu->login ($nick,$password);

        if($existeUsuario!=""){
            $id=$existeUsuario;
            $_SESSION['idUsuario']= $id;
            if($id==true){
            	if($id == 1){
            		$this->load->view ('objetoreservable/editarAulas');
            	}
                else{
                	$this->load->view ('templates/headerPerfil');
                	$this->load->view ('usuario/perfil2');
                	$this->load->view ('templates/footerPerfil');
                }
                $anyo= time()+31536000;
                if($remember==TRUE) {
                    setcookie ('recuerdame', $_POST ['user'], $anyo);
                }
            }
        }
        else{
            $this->load->view ('templates/header3');
            $this->load->view ('errors/noUsuario');
            $this->load->view ('templates/footer3');
        }
    }

    /*----- Listar usuarios -----*/

	public function listar(){
		$this->load->model ('Model_Usuario', 'mu');
		$usuarios= $this->mu->getTodos();
		$datos['usuarios']= $usuarios;

        $this->load->view('templates/header');
		$this->load->view ('usuario/listar', $datos);
		$this->load->view ('templates/footer');
	}

    /*----- Modificar los datos de tu usuario -----*/

	public function modificar(){
		$this->load->view ('templates/header');
		$this->load->view ('usuario/modificar');
		$this->load->view ('templates/footer');
	}
	
	public function modificarPost(){
	//recojo los datos que introducir� a modificar()
		$correo= $_REQUEST ['correo'];
		$nick= $_REQUEST ['nick'];
		$password= $_REQUEST ['password'];
	
	//De alguna manera debo recoger el usuario que quiere hacer la modificaci�n
	$id= $_REQUEST['id'];
		
		$this->load->model ('Model_Usuario', 'mu');
		$usuario= $this ->mu->modify ($id, $correo,$nick, $password);
        $this->load->view ('templates/header');
        $this->load->view ('usuario/modificarPost');
        $this->load->view ('templates/header');
    }

    /*----- Acceder al perfil de tu usuario -----*/

    public function perfil() {
        $id= isset ($_SESSION['idUsuario']) ? $_SESSION ['idUsuario'] : null;
        $datos ['idUsuario']=$id;
        $this->load->view ('templates/headerPerfil');
        $this->load->view ('usuario/perfil2', $datos);
        $this->load->view ('templates/footerPerfil');
    }

	public function perfilPost() {
		$nick= $_REQUEST ['nick'];
		$passActual= $_REQUEST ['passwordActual'];
		$password= $_REQUEST ['passwordNueva'];
		$password2= $_REQUEST['passwordNueva2'];
		$correo= $_REQUEST ['correo'];
		$res= $_REQUEST ['res'];
		
		if($res!=false) {
			$idUsuario= $_SESSION['idUsuario'];
			$this->load->model ('Model_Usuario','mu');
			
			$passwordCorrecta= $this->mu->encontrarUsuarioPorPassword($passActual);
			
			if($passwordCorrecta){
				$nombre = $_SESSION['idUsuario'].".jpg";
				//echo $nombre;
				$carpeta = "C://xampp/htdocs/ProyectoCalendario/assets/imagenes/perfil/";
				//copy ( $_FILES['imagenUsuario']['tmp_name'], $carpeta . $nombre );
				
				//echo "El fichero $nombre se almacen&oacute; en $carpeta";
				//return "<img src=".base_url()."assets/imagenes/perfil/".$nombre.">";
				mkdir(base_url()."assets/imagenes/perfil", 0777, true);
				move_uploaded_file($_FILES['imagenPerfil']['tmp_name'], $carpeta.$nombre);
				//$datos['imagen']= "<img style='width: 60px;height: 60px;border-radius:50%;' src=".base_url().'assets/imagenes/perfil/'.$nombre.">";
				$datos['imagen']= $nombre;
				//echo $datos['imagen'];
				
				if($idUsuario!=0) {
					$this->mu->cambiarPerfil ($idUsuario, $nick, $password, $correo);
	
	                $this->load->view ('templates/headerPerfil');
	                //$this->load->view ('usuario/perfilPost', $datos);
					
					$this->load->view('usuario/perfil2', $datos);
	                $this->load->view('templates/footerPerfil');
	            }
				else{
	                $this->load->view('templates/header3');
	                $this->load->view('errors/noPassword');
	                $this->load->view('templates/footer3');
	            }
			}
			else{
	            $this->load->view('templates/header3');
	            $this->load->view('errors/noPassword');
	            $this->load->view('templates/footer3');
	        }
			
			
		}
		else {
            $this->load->view ('templates/header3');
            $this->load->view ('errors/noModificarPerfil');
            $this->load->view ('templates/footer3');
        }
		
	}

    /*----- Recuperar contraseña de la cuenta -----*/

	public function recuperar() {
		$this->load->view ('templates/headerSinCabecera');
		$this->load->view('usuario/recuperar');
		$this->load->view ('templates/footer3');
	}
	
	public function recuperarPost() {
		$correo= $_REQUEST ['correo'];
		
		$this->load->model ('Model_Usuario','mu');
		$existeCorreo= $this->mu->comprobarCorreo ($correo);
		
		if($existeCorreo!= "") {
			$id= $existeCorreo;
			$_SESSION ['idUsuario']=$id;
			
			//Vamos a crear la cadena aleatoria que ser� la nueva contrase�a					
			$length= 5;
			$cadena= (str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ", 0, $length));
			
			$this->mu->cambiarPass($correo,$cadena);
			
			//El mensaje va junto. En el se adjuntar�n la cadena aleatoria y el nick.
			$mensaje="Restablece tus datos.
			Hemos recibido una petici&oacute;n para restablecer los datos de tu cuenta.
			Nueva contrase&ntilde;a ".$cadena."
			Nombre de usuario";
			$cabeceras='MIME-Version: 1.0' . "\r\n";
			$cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$cabeceras .= 'From: Servidor <recuperacion.reyfernando@gmail.com>' . "\r\n";
			// Se envia el correo al usuario
			mail($correo, "Recuperar contrase&ntilde;a", $mensaje, $cabeceras);
		}
		else {
			$this->load->view('errors/noCorreo');
		}
	}
	public function contacto()
	{
		$id= isset ($_SESSION['idUsuario']) ? $_SESSION ['idUsuario'] : null;
		$datos ['idUsuario']=$id;
		$this->load->view ('templates/header3');
		$this->load->view ('usuario/contacto', $datos);
		$this->load->view ('templates/footer3');
	}
}
?>