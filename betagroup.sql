-- OJO DESDE AQUÍ SE EMPIEZA A EJECUTAR DESDE EL ESQUEMA ADMIN_SYS

CREATE USER betagroup IDENTIFIED BY beta123;

-- Permisos básicos
GRANT CONNECT, RESOURCE TO betagroup;
GRANT UNLIMITED TABLESPACE TO betagroup;

-- Crear objetos necesarios para autoincremento
GRANT CREATE SEQUENCE TO betagroup;
GRANT CREATE TRIGGER TO betagroup;
GRANT CREATE PROCEDURE TO betagroup;

-- (Opcional) Si deseas crear en otros esquemas
-- GRANT CREATE ANY SEQUENCE TO betagroup;
-- GRANT CREATE ANY TRIGGER TO betagroup;



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

-- Crear tabla TIPO_CLINICA
CREATE TABLE TIPO_CLINICA (
  ID_TIPO_CLINICA NUMBER PRIMARY KEY,
  DESCRIPCION VARCHAR2(100) NOT NULL
);

-- Crear tabla CLIENTE (FK a TIPO_CLINICA)
CREATE TABLE CLIENTE (
  ID_USUARIO NUMBER PRIMARY KEY,
  NOMBRE_CLIENTE VARCHAR2(100) NOT NULL,
  CORREO VARCHAR2(100) NOT NULL,
  ID_TIPO_CLINICA NUMBER,
  CONSTRAINT FK_CLIENTE_TIPO_CLINICA FOREIGN KEY (ID_TIPO_CLINICA)
    REFERENCES TIPO_CLINICA(ID_TIPO_CLINICA)
);






-- ------------------------------------------------- PROCEDIMIENTOS ALMACENADOS ---------------------------------------------------------------------

-- 1. Función para encriptar la contraseña usando SHA-256
CREATE OR REPLACE FUNCTION HASH_PASSWORD(p_pass VARCHAR2) RETURN VARCHAR2 IS
BEGIN
  -- Convierte la contraseña a un hash SHA-256 y lo devuelve en hexadecimal
  RETURN RAWTOHEX(
    DBMS_CRYPTO.HASH(
      UTL_I18N.STRING_TO_RAW(p_pass, 'AL32UTF8'), -- convierte el texto en bytes
      DBMS_CRYPTO.HASH_SH256                      -- aplica el algoritmo SHA-256
    )
  );
END;
/

-- 2. Procedimiento para validar si el login es correcto
CREATE OR REPLACE PROCEDURE VALIDAR_LOGIN (
    p_correo IN VARCHAR2,         -- correo que ingresa el usuario
    p_pass IN VARCHAR2,           -- contraseña que ingresa el usuario
    p_resultado OUT NUMBER,       -- 1 = login correcto, 0 = incorrecto
    p_id_usuario OUT NUMBER,      -- se devuelve el ID del usuario si es correcto
    p_nombre OUT VARCHAR2,        -- se devuelve el nombre del usuario
    p_rol OUT VARCHAR2            -- se devuelve el rol (ej: admin o vendedor)
) IS
    v_hash VARCHAR2(64);          -- variable para almacenar el hash de la contraseña
BEGIN
    -- Hasheamos la contraseña ingresada para compararla con la guardada
    v_hash := HASH_PASSWORD(p_pass);

    -- Buscamos un usuario que tenga ese correo y contraseña
    SELECT id_usuario, nombre_usuario, rol
    INTO p_id_usuario, p_nombre, p_rol
    FROM USUARIO
    WHERE correo = p_correo AND contrasena = v_hash;

    -- Si encontró el usuario, el login es válido
    p_resultado := 1;

EXCEPTION
    WHEN NO_DATA_FOUND THEN
        -- Si no se encontró, el login es inválido
        p_resultado := 0;
END;
/

-- 3. Procedimiento que devuelve todos los usuarios usando un cursor
CREATE OR REPLACE PROCEDURE LISTAR_USUARIOS(p_cursor OUT SYS_REFCURSOR) AS
BEGIN
    -- Abrimos el cursor con los datos de todos los usuarios
    OPEN p_cursor FOR
        SELECT ID_USUARIO, NOMBRE_USUARIO, TELEFONO, CORREO, ROL, FECHA_REGISTRO
        FROM USUARIO
    ORDER BY ID_USUARIO;
END;
/

-- 4. Procedimiento PL/SQL para crear automáticamente una secuencia y un trigger
-- que permiten autoincrementar el ID de cualquier tabla que indiquemos

CREATE OR REPLACE PROCEDURE CREAR_AUTOINCREMENTO (
    p_tabla       IN VARCHAR2,       -- nombre de la tabla (ej: 'USUARIO')
    p_campo_id    IN VARCHAR2        -- nombre del campo ID (ej: 'ID_USUARIO')
) AS
    v_seq_name    VARCHAR2(100);     -- nombre que se usará para la secuencia
    v_trigger_name VARCHAR2(100);    -- nombre que se usará para el trigger
BEGIN
    -- Construimos los nombres de la secuencia y del trigger en base al nombre de la tabla
    v_seq_name := 'SEQ_ID_' || UPPER(p_tabla);
    v_trigger_name := 'TRG_AUTOINC_' || UPPER(p_tabla);

    -- Intentamos eliminar la secuencia anterior (si ya existía), para evitar errores
    EXECUTE IMMEDIATE '
        BEGIN
            EXECUTE IMMEDIATE ''DROP SEQUENCE ' || v_seq_name || ''';
        EXCEPTION
            WHEN OTHERS THEN NULL; -- Si no existe, no pasa nada
        END;';

    -- Creamos la nueva secuencia desde 1, que se usará para generar los IDs
    EXECUTE IMMEDIATE '
        CREATE SEQUENCE ' || v_seq_name || '
        START WITH 1
        INCREMENT BY 1
        NOCACHE
        NOCYCLE';

    -- Creamos el trigger que se ejecuta automáticamente antes de cada INSERT
    -- Si no se indica un ID, el trigger asigna uno usando la secuencia
    EXECUTE IMMEDIATE '
        CREATE OR REPLACE TRIGGER ' || v_trigger_name || '
        BEFORE INSERT ON ' || p_tabla || '
        FOR EACH ROW
        WHEN (NEW.' || p_campo_id || ' IS NULL)
        BEGIN
            SELECT ' || v_seq_name || '.NEXTVAL INTO :NEW.' || p_campo_id || ' FROM DUAL;
        END;';
END;
/

-- 5. Procedimiento que inserta un usuario

CREATE OR REPLACE PROCEDURE insertar_usuario (

    p_nombre     IN USUARIO.NOMBRE_USUARIO%TYPE,
    p_contrasena IN VARCHAR2,
    p_telefono   IN USUARIO.TELEFONO%TYPE,
    p_correo     IN USUARIO.CORREO%TYPE,
    p_rol        IN USUARIO.ROL%TYPE
) AS
BEGIN

    INSERT INTO USUARIO (
        NOMBRE_USUARIO,
        CONTRASENA,
        TELEFONO,
        CORREO,
        ROL
    ) VALUES (
        p_nombre,
        HASH_PASSWORD(p_contrasena),
        p_telefono,
        p_correo,
        p_rol
    );

END;
/

-- 6. Procedimiento para actualizar usuario

CREATE OR REPLACE PROCEDURE actualizar_usuario (
    p_id_usuario  IN USUARIO.ID_USUARIO%TYPE,
    p_nombre      IN USUARIO.NOMBRE_USUARIO%TYPE,
    p_contrasena  IN VARCHAR2, -- Puede venir NULL si no se quiere actualizar
    p_telefono    IN USUARIO.TELEFONO%TYPE,
    p_correo      IN USUARIO.CORREO%TYPE,
    p_rol         IN USUARIO.ROL%TYPE
) AS
BEGIN
    UPDATE USUARIO
    SET
        NOMBRE_USUARIO = p_nombre,
        TELEFONO       = p_telefono,
        CORREO         = p_correo,
        ROL            = p_rol,
        CONTRASENA     = CASE
                           WHEN p_contrasena IS NOT NULL THEN HASH_PASSWORD(p_contrasena)
                           ELSE CONTRASENA
                         END
    WHERE ID_USUARIO = p_id_usuario;
END;
/


CREATE OR REPLACE PROCEDURE actualizar_usuario_sc (
    p_id_usuario  IN USUARIO.ID_USUARIO%TYPE,
    p_nombre      IN USUARIO.NOMBRE_USUARIO%TYPE,

    p_telefono    IN USUARIO.TELEFONO%TYPE,
    p_correo      IN USUARIO.CORREO%TYPE,
    p_rol         IN USUARIO.ROL%TYPE
) AS
BEGIN
    UPDATE USUARIO
    SET
        NOMBRE_USUARIO = p_nombre,
        TELEFONO       = p_telefono,
        CORREO         = p_correo,
        ROL            = p_rol
       
    WHERE ID_USUARIO = p_id_usuario;
END;
/
-- 7. Procedimiento para eliminar usuario

CREATE OR REPLACE PROCEDURE eliminar_usuario (
    p_id IN USUARIO.ID_USUARIO%TYPE
) AS
BEGIN
    DELETE FROM USUARIO
    WHERE ID_USUARIO = p_id;
END;
/

-- 8. Procedimiento para crear cliente

CREATE OR REPLACE PROCEDURE insertar_cliente (
    p_id_usuario      IN CLIENTE.ID_USUARIO%TYPE,
    p_nombre_cliente  IN CLIENTE.NOMBRE_CLIENTE%TYPE,
    p_correo          IN CLIENTE.CORREO%TYPE,
    p_id_tipo_clinica IN CLIENTE.ID_TIPO_CLINICA%TYPE
) AS
BEGIN
    INSERT INTO CLIENTE (
        ID_USUARIO,
        NOMBRE_CLIENTE,
        CORREO,
        ID_TIPO_CLINICA
    ) VALUES (
        p_id_usuario,
        p_nombre_cliente,
        p_correo,
        p_id_tipo_clinica
    );
END;
/

-- 9. Procedimiento para actualizar cliente

CREATE OR REPLACE PROCEDURE actualizar_cliente (
    p_id_usuario      IN CLIENTE.ID_USUARIO%TYPE,
    p_nombre_cliente  IN CLIENTE.NOMBRE_CLIENTE%TYPE,
    p_correo          IN CLIENTE.CORREO%TYPE,
    p_id_tipo_clinica IN CLIENTE.ID_TIPO_CLINICA%TYPE
) AS
BEGIN
    UPDATE CLIENTE
    SET
        NOMBRE_CLIENTE  = p_nombre_cliente,
        CORREO          = p_correo,
        ID_TIPO_CLINICA = p_id_tipo_clinica
    WHERE ID_USUARIO = p_id_usuario;
END;
/

-- 10. Procedimiento para actualizar cliente

CREATE OR REPLACE PROCEDURE eliminar_cliente (
    p_id_usuario IN CLIENTE.ID_USUARIO%TYPE
) AS
BEGIN
    DELETE FROM CLIENTE
    WHERE ID_USUARIO = p_id_usuario;
END;
/

-- 11. Procedimiento para actualizar cliente

CREATE OR REPLACE PROCEDURE listar_clientes (
    p_cursor OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT c.ID_USUARIO,
               c.NOMBRE_CLIENTE,
               c.CORREO,
               tc.DESCRIPCION AS TIPO_CLINICA
        FROM CLIENTE c
        LEFT JOIN TIPO_CLINICA tc ON c.ID_TIPO_CLINICA = tc.ID_TIPO_CLINICA;
END;
/



-- -------------------------- DATOS Y PRUEBAS ------------------------------------------------------

-- Llamamos al procedimiento para crear la secuencia y trigger para la tabla USUARIO
BEGIN
    CREAR_AUTOINCREMENTO('USUARIO', 'ID_USUARIO');
END;
/

-- Insertamos datos en la tabla USUARIO
-- No se especifica ID_USUARIO porque se genera automáticamente por el trigger
-- La contraseña se guarda encriptada con HASH_PASSWORD

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL)
VALUES ('admin', HASH_PASSWORD('a'), '', 'admin@gmail.com', 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL)
VALUES ('Vendedor 1', HASH_PASSWORD('a'), '', 'vendedor@gmail.com', 0);

-- Mostramos los usuarios insertados (veremos el hash, no la contraseña original)
SELECT NOMBRE_USUARIO, CONTRASENA FROM USUARIO;

-- Confirmamos los cambios
COMMIT;

-- Si necesitas borrar todos los usuarios para hacer pruebas de nuevo, puedes usar esta línea:
-- DELETE FROM USUARIO;

SELECT NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL 
                        FROM USUARIO 
                        WHERE ID_USUARIO = 1

--Insertar una clinica
INSERT INTO TIPO_CLINICA (ID_TIPO_CLINICA, DESCRIPCION)
VALUES (1, 'Clínica General');

--Insertar un cliente
INSERT INTO CLIENTE (ID_USUARIO, NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)
VALUES (1, 'María Jiménez', 'maria.jimenez@gmail.com', 1);





 






