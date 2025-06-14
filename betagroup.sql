/*
Con�ctate como un usuario con privilegios de DBA (por ejemplo, SYS AS SYSDBA).

Ejecut� este script para crear un nuevo usuario:
*/


CREATE USER betagroup IDENTIFIED BY beta123;

-- Darle permisos b�sicos:
GRANT CONNECT, RESOURCE TO betagroup;
-- (Opcional para pruebas)
GRANT UNLIMITED TABLESPACE TO betagroup;


/*Crear un nuevo tablespace*/

CREATE TABLESPACE tbs_betagroup
DATAFILE 'C:\Oracle\oradata\ORCL\tbs_betagroup.dbf' SIZE 50M AUTOEXTEND ON NEXT 10M;

ALTER USER betagroup DEFAULT TABLESPACE tbs_betagroup;

commit


/*
Crear tabla inicial (USUARIOS)

*/


CREATE TABLE USUARIO (
  ID_USUARIO NUMBER PRIMARY KEY,
  NOMBRE_USUARIO VARCHAR2(100) NOT NULL,
  CONTRASENA VARCHAR2(100) NOT NULL,
  TELEFONO VARCHAR2(20),
  CORREO VARCHAR(50) NOT NULL,
  ROL NUMBER,
  FECHA_REGISTRO DATE DEFAULT SYSDATE
);

INSERT INTO USUARIO (ID_USUARIO, NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL)
VALUES (1, 'admin', 'a','','admin@gmail.com',1);

COMMIT;

/*
 En este punto tienen que crear una nueva conexi�n, 
 la pueden llamar betagroup igual que yo la llam�
 llenar el formulario de nueva conexion:

Nombre conexi�n		BetaGroup (o como quieras)
Nombre de usuario	betagroup
Contrase�a		beta123 (la que usaste)
Host			localhost
Puerto			1521
Tipo de conexi�n	TNS O BASICO
Tipo de conexi�n	ORCL o XE (dependiendo de tu instalaci�n)

conectarse con BETAGROUP. Yo escog� el color verde para la conexi�n

*/

-- con esta consulta vemos cual service usamos en nuestra base de datos
SELECT sys_context('userenv', 'service_name') FROM dual;
-- en mi pc es ORCL, si en sus pc es diferente, hay que cambiarlo en el sitio web de betagroup o me avisan


/*
AHORA HAY QUE HACER TODO ESTO:

 Editar el archivo php.ini
 C:\xampp\php\php.ini
 
 Busc� esta l�nea (us� Ctrl+F):
 ;extension=oci8_12c

Reemplazala por:
extension=oci8_19 // si us�s Oracle 19c

Instalar Oracle Instant Client
https://www.oracle.com/database/technologies/instant-client/downloads.html

Descarg� la versi�n Basic Light y descomprimila en una carpeta como:
C:\Oracle\instantclient\instantclient_19_26

Luego, agregar esa carpeta a la variable de entorno PATH:

Abr� "Configuraci�n del sistema" ? Variables de entorno 
* eso se hace presionando inicio, 
* en el buscador de windows poner: editar las variables de entorno del sistema
* al presionar el acceso se abre una ventana peque�a, ir al boton: Variables de Entorno
* se abre otra ventana, en el cuadro Variables de Sistema, buscar Path
* Selecionar Path, y darle editar
* NO BORREN NADA DE AH�
* darle Nuevo, y agregar la direccion del instaclient
    C:\Oracle\instantclient\instantclient_19_26
    
guardar y reiniciar el Apache (no hay que reiniciar la pc)


*/













