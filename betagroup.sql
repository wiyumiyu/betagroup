-- OJO DESDE AQUÍ SE EMPIEZA A EJECUTAR DESDE EL ESQUEMA ADMIN_SYS

CREATE USER betagroup IDENTIFIED BY beta123;

-- Darle permisos básicos:
GRANT CONNECT, RESOURCE TO betagroup;
-- (Opcional para pruebas)
GRANT UNLIMITED TABLESPACE TO betagroup;

GRANT EXECUTE ON DBMS_CRYPTO TO betagroup;


/*Crear un nuevo tablespace*/

CREATE TABLESPACE tbs_betagroup
DATAFILE 'C:\Oracle\oradata\ORCL\tbs_betagroup.dbf' SIZE 50M AUTOEXTEND ON NEXT 10M;

ALTER USER betagroup DEFAULT TABLESPACE tbs_betagroup;

commit


-- OJO DESDE AQUÍ SE EMPIEZA A EJECUTAR DESDE EL ESQUEMA BETAGROUP

-- ------------------------------------------------- TABLAS ---------------------------------------------------------------------

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

-- ------------------------------------------------- PROCEDIMIENTOS ALMACENADOS ---------------------------------------------------------------------

-- Función para encriptar la contraseña

CREATE OR REPLACE FUNCTION HASH_PASSWORD(p_pass VARCHAR2) RETURN VARCHAR2 IS
BEGIN
  RETURN RAWTOHEX(DBMS_CRYPTO.HASH(UTL_I18N.STRING_TO_RAW(p_pass, 'AL32UTF8'), DBMS_CRYPTO.HASH_SH256));
END;
/

-- Función para validar el login 
CREATE OR REPLACE PROCEDURE VALIDAR_LOGIN (
    p_correo IN VARCHAR2,
    p_pass IN VARCHAR2,
    p_resultado OUT NUMBER,
    p_id_usuario OUT NUMBER,
    p_nombre OUT VARCHAR2,
    p_rol OUT VARCHAR2
) IS
    v_hash VARCHAR2(64);
BEGIN
    v_hash := HASH_PASSWORD(p_pass);

    SELECT id_usuario, nombre_usuario, rol
    INTO p_id_usuario, p_nombre, p_rol
    FROM USUARIO
    WHERE correo = p_correo AND contrasena = v_hash;

    p_resultado := 1; -- login válido

EXCEPTION
    WHEN NO_DATA_FOUND THEN
        p_resultado := 0; -- login inválido
END;
/

-- -------------------------- DATOS Y PRUEBAS ------------------------------------------------------ 

INSERT INTO USUARIO (ID_USUARIO, NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL)
VALUES (1, 'admin', HASH_PASSWORD('a'), '', 'admin@gmail.com', 1);

SELECT NOMBRE_USUARIO, CONTRASENA FROM USUARIO;
COMMIT;













