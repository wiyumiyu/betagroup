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

-- Tabla de PROVEEDOR
CREATE TABLE PROVEEDOR (
  ID_PROVEEDOR NUMBER PRIMARY KEY,
  NOMBRE_PROVEEDOR VARCHAR2(100) NOT NULL,
  CORREO VARCHAR2(50) NOT NULL,
  DIRECCION_PROVEEDOR VARCHAR2(100) NOT NULL,
  FECHA_REGISTRO DATE DEFAULT SYSDATE
);

-- Crear tabla TIPO_CLINICAAdd commentMore actions
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

-- Tabla de TELEFONO_PROVEEDOR
CREATE TABLE TELEFONO_PROVEEDOR (
  ID_TELEFONO NUMBER PRIMARY KEY,
  TELEFONO VARCHAR2(20) NOT NULL,
  ID_PROVEEDOR NUMBER NOT NULL,
  CONSTRAINT FK_TEL_PROV FOREIGN KEY (ID_PROVEEDOR) REFERENCES PROVEEDOR(ID_PROVEEDOR)
);

-- Tabla de CATEGORIA
CREATE TABLE CATEGORIA (
  ID_CATEGORIA NUMBER PRIMARY KEY,
  NOMBRE_CATEGORIA VARCHAR2(100) NOT NULL
);

-- Tabla de PRODUCTO
CREATE TABLE PRODUCTO (
  ID_PRODUCTO NUMBER PRIMARY KEY,
  NOMBRE_PRODUCTO VARCHAR2(100) NOT NULL,
  PRECIO NUMBER NOT NULL,
  FECHA_REGISTRO DATE DEFAULT SYSDATE,
  ID_PROVEEDOR NUMBER NOT NULL,
  ID_CATEGORIA NUMBER NOT NULL,
  CONSTRAINT FK_PROD_PROV FOREIGN KEY (ID_PROVEEDOR) REFERENCES PROVEEDOR(ID_PROVEEDOR),
  CONSTRAINT FK_PROD_CAT FOREIGN KEY (ID_CATEGORIA) REFERENCES CATEGORIA(ID_CATEGORIA)
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

-- 7. Procedimiento para actualizar usuario sin contraseña

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
-- 8. Procedimiento para eliminar usuario

CREATE OR REPLACE PROCEDURE eliminar_usuario (
    p_id IN USUARIO.ID_USUARIO%TYPE
) AS
BEGIN
    DELETE FROM USUARIO
    WHERE ID_USUARIO = p_id;
END;
/

-- 9. Procedimiento para crear clienteAdd commentMore actions

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

-- 10. Procedimiento para actualizar cliente

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

-- 11. Procedimiento para actualizar cliente

CREATE OR REPLACE PROCEDURE eliminar_cliente (
    p_id_usuario IN CLIENTE.ID_USUARIO%TYPE
) AS
BEGIN
    DELETE FROM CLIENTE
    WHERE ID_USUARIO = p_id_usuario;
END;
/

-- 12. Procedimiento para actualizar cliente

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
/Add commentMore actions


-- 13. Procedimiento que devuelve todos los productos usando un cursor
CREATE OR REPLACE PROCEDURE LISTAR_PRODUCTOS(p_cursor OUT SYS_REFCURSOR) AS
BEGIN
    OPEN p_cursor FOR
        SELECT 
            P.ID_PRODUCTO,
            P.NOMBRE_PRODUCTO,
            P.PRECIO,
            P.FECHA_REGISTRO,
            PR.NOMBRE_PROVEEDOR,
            C.NOMBRE_CATEGORIA
        FROM PRODUCTO P
        JOIN PROVEEDOR PR ON P.ID_PROVEEDOR = PR.ID_PROVEEDOR
        JOIN CATEGORIA C ON P.ID_CATEGORIA = C.ID_CATEGORIA
        ORDER BY P.ID_PRODUCTO;
END;
/

-- 14. Procedimiento que inserta un producto

CREATE OR REPLACE PROCEDURE insertar_producto (
    p_nombre_producto   IN PRODUCTO.NOMBRE_PRODUCTO%TYPE,
    p_precio            IN PRODUCTO.PRECIO%TYPE,
    p_id_proveedor      IN PRODUCTO.ID_PROVEEDOR%TYPE,
    p_id_categoria      IN PRODUCTO.ID_CATEGORIA%TYPE
) AS
BEGIN
    INSERT INTO PRODUCTO (
        NOMBRE_PRODUCTO,
        PRECIO,
        ID_PROVEEDOR,
        ID_CATEGORIA
    ) VALUES (
        p_nombre_producto,
        p_precio,
        p_id_proveedor,
        p_id_categoria
    );
END;
/


-- 15. Procedimiento para actualizar un producto

CREATE OR REPLACE PROCEDURE actualizar_producto (
    p_id_producto       IN PRODUCTO.ID_PRODUCTO%TYPE,
    p_nombre_producto   IN PRODUCTO.NOMBRE_PRODUCTO%TYPE,
    p_precio            IN PRODUCTO.PRECIO%TYPE,
    p_id_proveedor      IN PRODUCTO.ID_PROVEEDOR%TYPE,
    p_id_categoria      IN PRODUCTO.ID_CATEGORIA%TYPE
) AS
BEGIN
    UPDATE PRODUCTO
    SET
        NOMBRE_PRODUCTO = p_nombre_producto,
        PRECIO = p_precio,
        ID_PROVEEDOR = p_id_proveedor,
        ID_CATEGORIA = p_id_categoria
    WHERE ID_PRODUCTO = p_id_producto;
END;
/

-- 16. Procedimiento para eliminar un producto

CREATE OR REPLACE PROCEDURE eliminar_producto (
    p_id IN PRODUCTO.ID_PRODUCTO%TYPE
) AS
BEGIN
    DELETE FROM PRODUCTO
    WHERE ID_PRODUCTO = p_id;
END;
/

-- 17. Procedimiento que devuelve todas las categorias 

CREATE OR REPLACE PROCEDURE LISTAR_CATEGORIAS(p_cursor OUT SYS_REFCURSOR) AS
BEGIN
    OPEN p_cursor FOR
        SELECT 
            C.ID_CATEGORIA,
            C.NOMBRE_CATEGORIA
        FROM CATEGORIA C
        ORDER BY C.ID_CATEGORIA;
END;
/

-- 18. Procedimiento que inserta una categoria

CREATE OR REPLACE PROCEDURE insertar_categoria (
    p_nombre_categoria IN CATEGORIA.NOMBRE_CATEGORIA%TYPE
) AS
BEGIN
    INSERT INTO CATEGORIA (
        NOMBRE_CATEGORIA
    ) VALUES (
        p_nombre_categoria
    );
END;
/

-- 19. Procedimiento para eliminar una categoria

CREATE OR REPLACE PROCEDURE eliminar_categoria (
    p_id IN CATEGORIA.ID_CATEGORIA%TYPE
) AS
BEGIN
    DELETE FROM CATEGORIA
    WHERE ID_CATEGORIA = p_id;
END;
/

-- 20. Procedimiento que devuelve todos los proveedores

CREATE OR REPLACE PROCEDURE LISTAR_PROVEEDORES(p_cursor OUT SYS_REFCURSOR) AS
BEGIN
  OPEN p_cursor FOR
    SELECT 
      ID_PROVEEDOR,
      NOMBRE_PROVEEDOR,
      CORREO,
      DIRECCION_PROVEEDOR,
      FECHA_REGISTRO
    FROM PROVEEDOR
    ORDER BY ID_PROVEEDOR;
END;
/

-- -------------------------- TRIGGER ------------------------------------------------------

-- 1. INSERTAR +506 al número

CREATE OR REPLACE TRIGGER TRG_TELEFONO_FORMATO
BEFORE INSERT ON TELEFONO_PROVEEDOR
FOR EACH ROW
BEGIN
  IF SUBSTR(:NEW.TELEFONO, 1, 4) != '+506' THEN
    :NEW.TELEFONO := '+506 ' || :NEW.TELEFONO;
  END IF;
END;


-- -------------------------- DATOS Y PRUEBAS ------------------------------------------------------

-- Llamamos al procedimiento para crear la secuencia y trigger para la tabla USUARIO
BEGIN
    CREAR_AUTOINCREMENTO('USUARIO', 'ID_USUARIO');
END;
/

BEGIN
    CREAR_AUTOINCREMENTO('PRODUCTO', 'ID_PRODUCTO');
    CREAR_AUTOINCREMENTO('CATEGORIA', 'ID_CATEGORIA');
    CREAR_AUTOINCREMENTO('PROVEEDOR', 'ID_PROVEEDOR');
    CREAR_AUTOINCREMENTO('TELEFONO_PROVEEDOR', 'ID_TELEFONO');
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

SELECT NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL Add commentMore actions
                        FROM USUARIO 
                        WHERE ID_USUARIO = 1

--Insertar una clinica
INSERT INTO TIPO_CLINICA (ID_TIPO_CLINICA, DESCRIPCION)
VALUES (1, 'Cl?nica General');

--Insertar un cliente
INSERT INTO CLIENTE (ID_USUARIO, NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)Add commentMore actions
VALUES (1, 'Mar?a Jim?nez', 'maria.jimenez@gmail.com', 1);


-- 1. Insertar proveedores correctamente (incluye dirección)
INSERT INTO PROVEEDOR (NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR)
VALUES ('BeautyTech S.A.', 'ventas@beautytech.com', 'San José, Costa Rica');

INSERT INTO PROVEEDOR (NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR)
VALUES ('Dermalaser CR', 'info@dermalaser.cr', 'Escazú, Costa Rica');

-- 2. Insertar teléfonos (ya existen los proveedores)
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('89842738', 1);

INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('72119988', 2);

-- 3. Insertar categoría
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA)
VALUES ('Equipos Estéticos');

-- 4. Insertar productos (ya existen proveedor y categoría)
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)
VALUES ('Soprano Titanium (Depilación Láser)', 42000000, 1, 1);

INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)
VALUES ('Multifuncional 6 en 1 con Lipoláser 850mz y EMS', 800000, 2, 1);

SELECT *
FROM PRODUCTO;

SELECT *
FROM TELEFONO_PROVEEDOR;

-- Ver procedimiento productos
VARIABLE rc REFCURSOR;
EXEC LISTAR_PRODUCTOS(:rc);
PRINT rc;
