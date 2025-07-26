-- OJO: Eliminacion de datos de BETAGROUP desde ADMIN SYS solo si se necesita
-- Eliminar perfil de seguridad
BEGIN
   EXECUTE IMMEDIATE 'DROP PROFILE perfil_seguro CASCADE';
EXCEPTION
   WHEN OTHERS THEN
      IF SQLCODE != -2380 THEN RAISE; END IF; -- 2380 = profile does not exist
END;
/

-- Eliminar roles
BEGIN
   EXECUTE IMMEDIATE 'DROP ROLE rol_administrador';
EXCEPTION
   WHEN OTHERS THEN
      IF SQLCODE != -1919 THEN RAISE; END IF; -- 1919 = role does not exist
END;
/

BEGIN
   EXECUTE IMMEDIATE 'DROP ROLE rol_cliente';
EXCEPTION
   WHEN OTHERS THEN
      IF SQLCODE != -1919 THEN RAISE; END IF;
END;
/

BEGIN
   EXECUTE IMMEDIATE 'DROP ROLE rol_proveedor';
EXCEPTION
   WHEN OTHERS THEN
      IF SQLCODE != -1919 THEN RAISE; END IF;
END;
/

-- Eliminar usuarios
BEGIN
   EXECUTE IMMEDIATE 'DROP USER betagroup CASCADE';
EXCEPTION
   WHEN OTHERS THEN
      IF SQLCODE != -1918 THEN RAISE; END IF; -- 1918 = user does not exist
END;
/

BEGIN
   EXECUTE IMMEDIATE 'DROP USER admin CASCADE';
EXCEPTION
   WHEN OTHERS THEN
      IF SQLCODE != -1918 THEN RAISE; END IF;
END;
/

BEGIN
   EXECUTE IMMEDIATE 'DROP USER client CASCADE';
EXCEPTION
   WHEN OTHERS THEN
      IF SQLCODE != -1918 THEN RAISE; END IF;
END;
/

BEGIN
   EXECUTE IMMEDIATE 'DROP USER prov CASCADE';
EXCEPTION
   WHEN OTHERS THEN
      IF SQLCODE != -1918 THEN RAISE; END IF;
END;
/

-- Eliminar TABLESPACE
BEGIN
   EXECUTE IMMEDIATE 'DROP TABLESPACE tbs_betagroup INCLUDING CONTENTS AND DATAFILES';
EXCEPTION
   WHEN OTHERS THEN
      IF SQLCODE != -959 THEN RAISE; END IF; -- 959 = tablespace does not exist
END;
/

COMMIT;

-- Crear TABLESPACES
CREATE TABLESPACE tbs_betagroup
    DATAFILE 'C:\Oracle\oradata\ORCL\tbs_betagroup.dbf'
    SIZE 50M AUTOEXTEND ON NEXT 10M;
    
-- Crear ROLES
CREATE ROLE rol_administrador;
GRANT DBA TO rol_administrador;

CREATE ROLE rol_cliente;
GRANT CREATE SESSION, CREATE TABLE, CREATE VIEW, CREATE SEQUENCE, CREATE PROCEDURE TO rol_cliente;

CREATE ROLE rol_proveedor;
GRANT CREATE SESSION, CREATE TABLE, CREATE VIEW, CREATE SEQUENCE, CREATE PROCEDURE TO rol_proveedor;

-- Crear PERFIL DE SEGURIDAD
CREATE PROFILE perfil_seguro LIMIT
FAILED_LOGIN_ATTEMPTS 3
PASSWORD_LIFE_TIME 30
PASSWORD_LOCK_TIME 1;

-- Crear USUARIOS y asignar roles y tablaspaces
CREATE USER admin IDENTIFIED BY admin
DEFAULT TABLESPACE tbs_betagroup
QUOTA UNLIMITED ON tbs_betagroup;
GRANT rol_administrador TO admin;

CREATE USER client IDENTIFIED BY client
DEFAULT TABLESPACE tbs_betagroup
QUOTA UNLIMITED ON tbs_betagroup
PROFILE perfil_seguro;
GRANT rol_cliente TO client;

CREATE USER prov IDENTIFIED BY prov
DEFAULT TABLESPACE tbs_betagroup
QUOTA UNLIMITED ON tbs_betagroup
PROFILE perfil_seguro;
GRANT rol_proveedor TO prov;

CREATE USER betagroup IDENTIFIED BY beta123
DEFAULT TABLESPACE tbs_betagroup;
GRANT rol_administrador TO betagroup;
GRANT EXECUTE ON DBMS_SESSION TO betagroup;
GRANT EXECUTE ON DBMS_CRYPTO TO betagroup;
-- Privilegios directos necesarios para ejecutar DDL dinámico desde PL/SQL
GRANT CREATE ANY SEQUENCE TO betagroup;
GRANT CREATE ANY TRIGGER TO betagroup;
GRANT CREATE ANY PROCEDURE TO betagroup;
GRANT CREATE ANY TABLE TO betagroup;
GRANT ALTER ANY TABLE TO betagroup;
GRANT DROP ANY TRIGGER TO betagroup;
GRANT DROP ANY SEQUENCE TO betagroup;
ALTER USER betagroup QUOTA UNLIMITED ON TBS_BETAGROUP;
commit

-- OJO DESDE AQUï¿½ SE EMPIEZA A EJECUTAR DESDE EL ESQUEMA BETAGROUP

-- ------------------------------------------------- TABLAS ---------------------------------------------------------------------

/* Crear tabla inicial (USUARIOS) */
CREATE TABLE USUARIO (
  ID_USUARIO NUMBER PRIMARY KEY,
  NOMBRE_USUARIO VARCHAR2(100) NOT NULL,
  CONTRASENA VARCHAR2(100) NOT NULL,
  TELEFONO VARCHAR2(20),
  CORREO VARCHAR2(50) NOT NULL,
  ROL NUMBER,
  FECHA_REGISTRO DATE DEFAULT SYSDATE,
  ESTADO NUMBER(1) DEFAULT 1 -- 1 = habilitado, 0 = deshabilitado
);


-- Tabla de PROVEEDOR
CREATE TABLE PROVEEDOR (
  ID_PROVEEDOR NUMBER PRIMARY KEY,
  NOMBRE_PROVEEDOR VARCHAR2(100) NOT NULL,
  CORREO VARCHAR2(50) NOT NULL,
  DIRECCION_PROVEEDOR VARCHAR2(100) NOT NULL,
  FECHA_REGISTRO DATE DEFAULT SYSDATE,
  ESTADO NUMBER(1) DEFAULT 1 -- 1 = habilitado, 0 = deshabilitado
);

-- Crear tabla TIPO_CLINICA
CREATE TABLE TIPO_CLINICA (
  ID_TIPO_CLINICA NUMBER PRIMARY KEY,
  DESCRIPCION VARCHAR2(100) NOT NULL
);

-- Crear tabla CLIENTE (FK a TIPO_CLINICA)
CREATE TABLE CLIENTE (
  ID_CLIENTE NUMBER PRIMARY KEY,
  NOMBRE_CLIENTE VARCHAR2(100) NOT NULL,
  CORREO VARCHAR2(100) NOT NULL,
  ID_TIPO_CLINICA NUMBER,
  CONSTRAINT FK_CLIENTE_TIPO_CLINICA FOREIGN KEY (ID_TIPO_CLINICA)
    REFERENCES TIPO_CLINICA(ID_TIPO_CLINICA)
);

-- Crear tabla Telefono Cliente
CREATE TABLE TELEFONO_CLIENTE (
  ID_TELEFONO NUMBER PRIMARY KEY,
  TELEFONO VARCHAR2(20) NOT NULL,
  ID_CLIENTE NUMBER NOT NULL,
  CONSTRAINT FK_TELEFONO_CLIENTE FOREIGN KEY (ID_CLIENTE)
    REFERENCES CLIENTE(ID_CLIENTE)
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

-- Tabla de VENTAS
CREATE TABLE VENTA (
  ID_VENTA NUMBER PRIMARY KEY,
  NUMERO NUMBER DEFAULT 0, 
  FECHA DATE DEFAULT SYSDATE,
  IMPUESTOS NUMBER DEFAULT 0,
  ID_CLIENTE NUMBER NOT NULL,
  ID_USUARIO NUMBER NOT NULL,
   ESTADO NUMBER(1) DEFAULT 1 ,
  CONSTRAINT FK_VENT_CLIE FOREIGN KEY (ID_CLIENTE) REFERENCES CLIENTE(ID_CLIENTE),
  CONSTRAINT FK_VENT_USUA FOREIGN KEY (ID_USUARIO) REFERENCES USUARIO(ID_USUARIO)
);

-- Tabla de VENTA_DETALLES
CREATE TABLE VENTA_DETALLE (
  ID_VENTA_DETALLE NUMBER PRIMARY KEY,
  CANTIDAD NUMBER DEFAULT 0,
  PRECIO_UNITARIO NUMBER DEFAULT 0,
  DESCUENTO NUMBER DEFAULT 0,
  ID_PRODUCTO NUMBER NOT NULL,
  ID_VENTA NUMBER NOT NULL,
  CONSTRAINT FK_VEDE_PROD FOREIGN KEY (ID_PRODUCTO) REFERENCES PRODUCTO(ID_PRODUCTO),
  CONSTRAINT FK_VEDE_VENT FOREIGN KEY (ID_VENTA) REFERENCES VENTA(ID_VENTA)
  
);

-- Tabla de BITACORA
CREATE TABLE BITACORA (
  ID_BITACORA    NUMBER  PRIMARY KEY,
  ID_USUARIO      NUMBER,
  FECHA_OPERACION TIMESTAMP DEFAULT SYSTIMESTAMP,
  DESCRIPCION     CLOB
);

-- ------------------------------------------------- ÍNDICES ---------------------------------------------------------------------

-- Índices para USUARIO
CREATE INDEX idx_usuario_nombre ON USUARIO (NOMBRE_USUARIO);
CREATE INDEX idx_usuario_estado ON USUARIO (ESTADO);

-- Índices para PROVEEDOR
CREATE INDEX idx_proveedor_nombre ON PROVEEDOR (NOMBRE_PROVEEDOR);
CREATE INDEX idx_proveedor_estado ON PROVEEDOR (ESTADO);

-- Índices para CLIENTE
CREATE INDEX idx_cliente_nombre ON CLIENTE (NOMBRE_CLIENTE);
CREATE INDEX idx_cliente_tipo ON CLIENTE (ID_TIPO_CLINICA);

-- Índices para TELEFONO_CLIENTE
CREATE INDEX idx_tel_cliente ON TELEFONO_CLIENTE (ID_CLIENTE);

-- Índices para TELEFONO_PROVEEDOR
CREATE INDEX idx_tel_proveedor ON TELEFONO_PROVEEDOR (ID_PROVEEDOR);

-- Índices para PRODUCTO
CREATE INDEX idx_producto_nombre ON PRODUCTO (NOMBRE_PRODUCTO);
CREATE INDEX idx_producto_prov_cat ON PRODUCTO (ID_PROVEEDOR, ID_CATEGORIA);

-- Índices para VENTA
CREATE INDEX idx_venta_fecha ON VENTA (FECHA);
CREATE INDEX idx_venta_cliente_usuario ON VENTA (ID_CLIENTE, ID_USUARIO);
CREATE INDEX idx_venta_estado ON VENTA (ESTADO);

-- Índices para VENTA_DETALLE
CREATE INDEX idx_vdetalle_venta ON VENTA_DETALLE (ID_VENTA);
CREATE INDEX idx_vdetalle_producto ON VENTA_DETALLE (ID_PRODUCTO);

-- Índices para CATEGORIA
CREATE INDEX idx_categoria_nombre ON CATEGORIA (NOMBRE_CATEGORIA);

-- Índices para TIPO_CLINICA
CREATE INDEX idx_tipoclinica_descripcion ON TIPO_CLINICA (DESCRIPCION);

-- Índices para BITACORA
CREATE INDEX idx_bitacora_usuario_fecha ON BITACORA (ID_USUARIO, FECHA_OPERACION);

-- ------------------------------------------------- PROCEDIMIENTOS ALMACENADOS ---------------------------------------------------------------------

-- 1. Funcion para encriptar la contrasena usando SHA-256
CREATE OR REPLACE FUNCTION HASH_PASSWORD(p_pass VARCHAR2) RETURN VARCHAR2 IS
BEGIN
  -- Convierte la contraseï¿½a a un hash SHA-256 y lo devuelve en hexadecimal
  RETURN RAWTOHEX(
    DBMS_CRYPTO.HASH(
      UTL_I18N.STRING_TO_RAW(p_pass, 'AL32UTF8'), -- convierte el texto en bytes
      DBMS_CRYPTO.HASH_SH256                      -- aplica el algoritmo SHA-256
    )
  );
END;
/

-- 2. Procedimiento para validar si el login es correcto
CREATE OR REPLACE PROCEDURE PROC_VALIDAR_LOGIN (
    p_correo       IN  VARCHAR2,
    p_pass         IN  VARCHAR2,
    p_resultado    OUT NUMBER,
    p_id_usuario   OUT NUMBER,
    p_nombre       OUT VARCHAR2,
    p_rol          OUT VARCHAR2
) IS
    v_hash VARCHAR2(64);
BEGIN
    -- Hash de la contraseï¿½a ingresada
    v_hash := HASH_PASSWORD(p_pass);

    -- Validar credenciales y estado
    SELECT id_usuario, nombre_usuario, rol
    INTO p_id_usuario, p_nombre, p_rol
    FROM USUARIO
    WHERE correo = p_correo
      AND contrasena = v_hash
      AND estado = 1;  -- Solo usuarios habilitados

    p_resultado := 1; -- login vï¿½lido

EXCEPTION
    WHEN NO_DATA_FOUND THEN
        p_resultado := 0; -- login invalido o usuario deshabilitado
END;
/

-- 3. Procedimiento que devuelve todos los usuarios usando un cursor
CREATE OR REPLACE PROCEDURE PROC_LISTAR_USUARIOS(p_cursor OUT SYS_REFCURSOR) AS
BEGIN
    OPEN p_cursor FOR
        SELECT ID_USUARIO, NOMBRE_USUARIO, TELEFONO, CORREO, ROL, FECHA_REGISTRO, ESTADO
        FROM USUARIO
        WHERE ESTADO = 1
        ORDER BY ID_USUARIO;
END;
/


-- 4. Procedimiento PL/SQL para crear automaticamente una secuencia y un trigger
-- que permiten autoincrementar el ID de cualquier tabla que indiquemos

CREATE OR REPLACE PROCEDURE PROC_CREAR_AUTOINCREMENTO (
    p_tabla       IN VARCHAR2,       -- nombre de la tabla (ej: 'USUARIO')
    p_campo_id    IN VARCHAR2        -- nombre del campo ID (ej: 'ID_USUARIO')
) AS
    v_seq_name    VARCHAR2(100);     -- nombre que se usarï¿½ para la secuencia
    v_trigger_name VARCHAR2(100);    -- nombre que se usarï¿½ para el trigger
BEGIN
    -- Construimos los nombres de la secuencia y del trigger en base al nombre de la tabla
    v_seq_name := 'SEQ_ID_' || UPPER(p_tabla);
    v_trigger_name := 'TRG_AUTOINC_' || UPPER(p_tabla);

    -- Intentamos eliminar la secuencia anterior (si ya existï¿½a), para evitar errores
    EXECUTE IMMEDIATE '
        BEGIN
            EXECUTE IMMEDIATE ''DROP SEQUENCE ' || v_seq_name || ''';
        EXCEPTION
            WHEN OTHERS THEN NULL; -- Si no existe, no pasa nada
        END;';

    -- Creamos la nueva secuencia desde 1, que se usarï¿½ para generar los IDs
    EXECUTE IMMEDIATE '
        CREATE SEQUENCE ' || v_seq_name || '
        START WITH 1
        INCREMENT BY 1
        NOCACHE
        NOCYCLE';

    -- Creamos el trigger que se ejecuta automï¿½ticamente antes de cada INSERT
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

CREATE OR REPLACE PROCEDURE PROC_insertar_usuario (
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
        ROL,
        ESTADO
    ) VALUES (
        p_nombre,
        HASH_PASSWORD(p_contrasena),
        p_telefono,
        p_correo,
        p_rol,
        1 -- Habilitado por defecto
    );
END;
/


-- 6. Procedimiento para actualizar usuario

CREATE OR REPLACE PROCEDURE PROC_actualizar_usuario (
    p_id_usuario  IN USUARIO.ID_USUARIO%TYPE,
    p_nombre      IN USUARIO.NOMBRE_USUARIO%TYPE,
    p_contrasena  IN VARCHAR2, -- Puede venir NULL si no se quiere actualizar
    p_telefono    IN USUARIO.TELEFONO%TYPE,
    p_correo      IN USUARIO.CORREO%TYPE,
    p_rol         IN USUARIO.ROL%TYPE,
    p_estado      IN USUARIO.ESTADO%TYPE
) AS
BEGIN
    UPDATE USUARIO
    SET
        NOMBRE_USUARIO = p_nombre,
        TELEFONO       = p_telefono,
        CORREO         = p_correo,
        ROL            = p_rol,
        ESTADO         = p_estado,
        CONTRASENA     = CASE
                           WHEN p_contrasena IS NOT NULL THEN HASH_PASSWORD(p_contrasena)
                           ELSE CONTRASENA
                         END
    WHERE ID_USUARIO = p_id_usuario;
END;
/


-- 7. Procedimiento para actualizar usuario sin contrasena
CREATE OR REPLACE PROCEDURE PROC_actualizar_usuario_sc (
    p_id_usuario  IN USUARIO.ID_USUARIO%TYPE,
    p_nombre      IN USUARIO.NOMBRE_USUARIO%TYPE,
    p_telefono    IN USUARIO.TELEFONO%TYPE,
    p_correo      IN USUARIO.CORREO%TYPE,
    p_rol         IN USUARIO.ROL%TYPE,
    p_estado      IN USUARIO.ESTADO%TYPE
) AS
BEGIN
    UPDATE USUARIO
    SET
        NOMBRE_USUARIO = p_nombre,
        TELEFONO       = p_telefono,
        CORREO         = p_correo,
        ROL            = p_rol,
        ESTADO         = p_estado
    WHERE ID_USUARIO = p_id_usuario;
END;
/

-- 8. Procedimiento para eliminar usuario

CREATE OR REPLACE PROCEDURE PROC_eliminar_usuario (
    p_id IN USUARIO.ID_USUARIO%TYPE
) AS
BEGIN
    DELETE FROM USUARIO
    WHERE ID_USUARIO = p_id;
END;
/

-- 9. Procedimiento para crear cliente

CREATE OR REPLACE PROCEDURE PROC_insertar_cliente (
    p_nombre_cliente  IN CLIENTE.NOMBRE_CLIENTE%TYPE,
    p_correo          IN CLIENTE.CORREO%TYPE,
    p_id_tipo_clinica IN CLIENTE.ID_TIPO_CLINICA%TYPE
) AS
BEGIN
    INSERT INTO CLIENTE (
        NOMBRE_CLIENTE,
        CORREO,
        ID_TIPO_CLINICA
    ) VALUES (
        p_nombre_cliente,
        p_correo,
        p_id_tipo_clinica
    );
END;
/

-- 10. Procedimiento para actualizar cliente

CREATE OR REPLACE PROCEDURE PROC_actualizar_cliente (
    p_id_cliente    IN CLIENTE.ID_cliente%TYPE,
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
    WHERE ID_CLIENTE = p_id_cliente;
END;
/

-- 11. Procedimiento para eliminar cliente

CREATE OR REPLACE PROCEDURE PROC_eliminar_cliente (
    p_id_cliente IN CLIENTE.ID_CLIENTE%TYPE
) AS
BEGIN
    -- Eliminar VENTA_DETALLE asociados a las ventas del cliente
    DELETE FROM VENTA_DETALLE
    WHERE ID_VENTA IN (
        SELECT ID_VENTA FROM VENTA WHERE ID_CLIENTE = p_id_cliente
    );

    -- Eliminar las VENTAS del cliente
    DELETE FROM VENTA
    WHERE ID_CLIENTE = p_id_cliente;

    -- Eliminar los telefonos asociados
    DELETE FROM TELEFONO_CLIENTE
    WHERE ID_CLIENTE = p_id_cliente;

    -- Eliminar el cliente
    DELETE FROM CLIENTE
    WHERE ID_CLIENTE = p_id_cliente;
END;
/

-- 12. Procedimiento para actualizar cliente

CREATE OR REPLACE PROCEDURE PROC_listar_clientes (
    p_cursor OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT c.ID_CLIENTE,
               c.NOMBRE_CLIENTE,
               c.CORREO,
               tc.DESCRIPCION AS TIPO_CLINICA
        FROM CLIENTE c
        LEFT JOIN TIPO_CLINICA tc ON c.ID_TIPO_CLINICA = tc.ID_TIPO_CLINICA
        ORDER BY c.ID_CLIENTE;
END;
/

-- 13. Procedimiento para insertar una clinica nueva

CREATE OR REPLACE PROCEDURE PROC_insertar_tipo_clinica (
    p_descripcion IN TIPO_CLINICA.DESCRIPCION%TYPE
) AS
BEGIN
    INSERT INTO TIPO_CLINICA (DESCRIPCION)
    VALUES (p_descripcion);
END;
/

-- 14. Procedimiento para actualizar una clinica

CREATE OR REPLACE PROCEDURE PROC_actualizar_tipo_clinica (
    p_id_tipo_clinica IN TIPO_CLINICA.ID_TIPO_CLINICA%TYPE,
    p_descripcion     IN TIPO_CLINICA.DESCRIPCION%TYPE
) AS
BEGIN
    UPDATE TIPO_CLINICA
    SET DESCRIPCION = p_descripcion
    WHERE ID_TIPO_CLINICA = p_id_tipo_clinica;
END;
/

-- 15. Procedimiento para eliminar una clinica

CREATE OR REPLACE PROCEDURE PROC_eliminar_tipo_clinica (
    p_id_tipo_clinica IN TIPO_CLINICA.ID_TIPO_CLINICA%TYPE
) AS
BEGIN
    DELETE FROM TIPO_CLINICA
    WHERE ID_TIPO_CLINICA = p_id_tipo_clinica;
END;
/

-- 16. Procedimiento para listar las clinicas

CREATE OR REPLACE PROCEDURE PROC_listar_tipos_clinica (
    p_cursor OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT ID_TIPO_CLINICA, DESCRIPCION
        FROM TIPO_CLINICA
        ORDER BY ID_TIPO_CLINICA;
END;
/

-- 17. Procedimiento que devuelve todos los productos usando un cursor
CREATE OR REPLACE PROCEDURE PROC_LISTAR_PRODUCTOS(p_cursor OUT SYS_REFCURSOR) AS
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

-- 18. Procedimiento que inserta un producto

CREATE OR REPLACE PROCEDURE PROC_insertar_producto (
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


-- 19. Procedimiento para actualizar un producto

CREATE OR REPLACE PROCEDURE PROC_actualizar_producto (
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

-- 20. Procedimiento para eliminar un producto

CREATE OR REPLACE PROCEDURE PROC_eliminar_producto (
    p_id IN PRODUCTO.ID_PRODUCTO%TYPE
) AS
BEGIN
    DELETE FROM PRODUCTO
    WHERE ID_PRODUCTO = p_id;
END;
/

-- 21. Procedimiento que devuelve todas las categorias 

CREATE OR REPLACE PROCEDURE PROC_LISTAR_CATEGORIAS(p_cursor OUT SYS_REFCURSOR) AS
BEGIN
    OPEN p_cursor FOR
        SELECT 
            C.ID_CATEGORIA,
            C.NOMBRE_CATEGORIA
        FROM CATEGORIA C
        ORDER BY C.ID_CATEGORIA;
END;
/

-- 22. Procedimiento que inserta una categoria

CREATE OR REPLACE PROCEDURE PROC_insertar_categoria (
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

-- 23. Procedimiento para eliminar una categoria

CREATE OR REPLACE PROCEDURE PROC_eliminar_categoria (
    p_id IN CATEGORIA.ID_CATEGORIA%TYPE
) AS
BEGIN
    DELETE FROM CATEGORIA
    WHERE ID_CATEGORIA = p_id;
END;
/

-- 24. Procedimiento que devuelve todos los proveedores

CREATE OR REPLACE PROCEDURE PROC_LISTAR_PROVEEDORES(p_cursor OUT SYS_REFCURSOR) AS
BEGIN
  OPEN p_cursor FOR
    SELECT 
      p.ID_PROVEEDOR,
      p.NOMBRE_PROVEEDOR,
      p.CORREO,
      p.DIRECCION_PROVEEDOR,
      p.FECHA_REGISTRO,
      LISTAGG(t.TELEFONO, CHR(10)) WITHIN GROUP (ORDER BY t.TELEFONO) AS TELEFONOS
    FROM 
      PROVEEDOR p
    LEFT JOIN 
      TELEFONO_PROVEEDOR t ON p.ID_PROVEEDOR = t.ID_PROVEEDOR
    WHERE 
      p.ESTADO = 1
    GROUP BY 
      p.ID_PROVEEDOR, p.NOMBRE_PROVEEDOR, p.CORREO, p.DIRECCION_PROVEEDOR, p.FECHA_REGISTRO
    ORDER BY 
      p.ID_PROVEEDOR;
END;
/

-- 25. Procedimiento para insertar_proveedor --

CREATE OR REPLACE PROCEDURE PROC_insertar_proveedor (
    p_nombre    IN proveedor.nombre_proveedor%TYPE,
    p_correo    IN proveedor.correo%TYPE,
    p_direccion IN proveedor.direccion_proveedor%TYPE,
    p_estado    IN proveedor.estado%TYPE DEFAULT 1,
    p_id_out    OUT proveedor.id_proveedor%TYPE
) AS
BEGIN
    INSERT INTO proveedor (
        nombre_proveedor,
        correo,
        direccion_proveedor,
        estado
    ) VALUES (
        p_nombre,
        p_correo,
        p_direccion,
        p_estado
    )
    RETURNING id_proveedor INTO p_id_out;
END;
/
   
-- 26. procedimiento para actualizar proveedor

CREATE OR REPLACE PROCEDURE PROC_actualizar_proveedor (
    p_id_proveedor IN proveedor.id_proveedor%TYPE,
    p_nombre       IN proveedor.nombre_proveedor%TYPE,
    p_correo       IN proveedor.correo%TYPE,
    p_direccion    IN proveedor.direccion_proveedor%TYPE,
    p_estado       IN proveedor.estado%TYPE
) AS
BEGIN
    UPDATE proveedor
    SET
        nombre_proveedor = p_nombre,
        correo = p_correo,
        direccion_proveedor = p_direccion,
        estado = p_estado
    WHERE
        id_proveedor = p_id_proveedor;
END;
/

-- 27. PROCEDIMIENTO PARA ELIMINAR PROVEEDOR

CREATE OR REPLACE PROCEDURE PROC_eliminar_proveedor (
    p_id_proveedor IN proveedor.id_proveedor%TYPE
) AS
BEGIN
    DELETE FROM proveedor
    WHERE id_proveedor = p_id_proveedor;
END;
/

-- 28. PROCEDIMIENTO PARA HABILITAR UN PROVEEDOR CAMBIANDO SU ESTADO A 1

CREATE OR REPLACE PROCEDURE PROC_habilitar_proveedor (
    p_id IN PROVEEDOR.ID_PROVEEDOR%TYPE
) AS
BEGIN
    UPDATE PROVEEDOR
    SET ESTADO = 1
    WHERE ID_PROVEEDOR = p_id;
END;
/

-- 29. PROCEDIMIENTO PARA OBTENER DATOS DE UN PROVEEDOR ESPECIFICO

CREATE OR REPLACE PROCEDURE PROC_OBTENER_PROVEEDOR (
    p_id       IN  NUMBER,
    p_cursor   OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, ESTADO
        FROM PROVEEDOR
        WHERE ID_PROVEEDOR = p_id;
END;
/

-- 30. PROCEDIMIENTO PARA OBTENER SOLO LOS ID DE TELEFONOS DE UN PROVEEDOR

CREATE OR REPLACE PROCEDURE PROC_OBTENER_ID_TELEFONOS (
    p_id_proveedor IN TELEFONO_PROVEEDOR.ID_PROVEEDOR%TYPE,
    p_cursor       OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT ID_TELEFONO
        FROM TELEFONO_PROVEEDOR
        WHERE ID_PROVEEDOR = p_id_proveedor;
END;
/

-- 31. PROCEDIMIENTO PARA OBTENER LOS TELEFONOS COMPLETOS DE UN PROVEEDOR

CREATE OR REPLACE PROCEDURE PROC_OBTENER_TELEFONOS_PROVEEDOR (
    p_id_proveedor IN TELEFONO_PROVEEDOR.ID_PROVEEDOR%TYPE,
    p_cursor       OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT ID_TELEFONO, TELEFONO
        FROM TELEFONO_PROVEEDOR
        WHERE ID_PROVEEDOR = p_id_proveedor;
END;
/

-- 32. PROCEDIMIENTO PARA ACTUALIZAR UN TELEFONO EXISTENTE

CREATE OR REPLACE PROCEDURE PROC_actualizar_telefono_proveedor (
    p_id_telefono   IN TELEFONO_PROVEEDOR.ID_TELEFONO%TYPE,
    p_telefono      IN TELEFONO_PROVEEDOR.TELEFONO%TYPE
) AS
BEGIN
    UPDATE TELEFONO_PROVEEDOR
    SET TELEFONO = p_telefono
    WHERE ID_TELEFONO = p_id_telefono;
END;
/

-- 33. PROCEDIMIENTO PARA ELIMINAR UN TELEFONO

CREATE OR REPLACE PROCEDURE PROC_eliminar_telefono (
    p_id_tel IN TELEFONO_PROVEEDOR.ID_TELEFONO%TYPE
)
AS
BEGIN
    DELETE FROM TELEFONO_PROVEEDOR
    WHERE ID_TELEFONO = p_id_tel;
END;
/

-- 34. PROCEDIMIENTO PARA INSERTAR UN NUEVO TELEFONO PARA UN PROVEEDOR

CREATE OR REPLACE PROCEDURE PROC_insertar_telefono_proveedor (
    p_id_proveedor IN TELEFONO_PROVEEDOR.ID_PROVEEDOR%TYPE,
    p_telefono     IN TELEFONO_PROVEEDOR.TELEFONO%TYPE
) AS
BEGIN
    INSERT INTO TELEFONO_PROVEEDOR (ID_PROVEEDOR, TELEFONO)
    VALUES (p_id_proveedor, p_telefono);
END;
/

  
-- 35. PROCEDIMIENTO PARA ELIMINAR UNA VENTA

CREATE OR REPLACE PROCEDURE PROC_eliminar_venta (
    p_id IN VENTA.ID_VENTA%TYPE
) AS
BEGIN
    DELETE FROM VENTA
    WHERE ID_VENTA = p_id;
END;
/

-- 36. PROCEDIMIENTO PARA ELIMINAR UNA VENTA_DETALLE

CREATE OR REPLACE PROCEDURE PROC_eliminar_venta_detalle (
    p_id IN VENTA_DETALLE.ID_VENTA_DETALLE%TYPE
) AS
BEGIN
    DELETE FROM VENTA_DETALLE
    WHERE ID_VENTA = p_id;
END;
/

-- 37. PROCEDIMIENTO QUE DEVUELVE TODAS LAS VENTAS USANDO UN CURSOR

CREATE OR REPLACE PROCEDURE PROC_LISTAR_VENTAS(
    p_cursor OUT SYS_REFCURSOR,
    p_estado IN VENTA.ESTADO%TYPE
)  AS
BEGIN
    -- Abrimos el cursor con los datos de todas las ventas
    OPEN p_cursor FOR
        SELECT VENTA.ID_VENTA, VENTA.NUMERO, VENTA.FECHA, VENTA.IMPUESTOS, CLIENTE.NOMBRE_CLIENTE, USUARIO.NOMBRE_USUARIO
        FROM VENTA
        LEFT JOIN CLIENTE ON CLIENTE.ID_CLIENTE = VENTA.ID_CLIENTE
        LEFT JOIN USUARIO ON USUARIO.ID_USUARIO = VENTA.ID_USUARIO
        WHERE VENTA.ESTADO = p_estado
        ORDER BY VENTA.NUMERO;
END;
/

-- 38. PROCEDIMIENTO PARA INSERTAR UNA VENTA

CREATE OR REPLACE PROCEDURE PROC_insertar_venta (
    p_numero     IN VENTA.NUMERO%TYPE,
    p_impuestos  IN VENTA.IMPUESTOS%TYPE,
    p_id_cliente IN VENTA.ID_CLIENTE%TYPE,
    p_id_usuario IN VENTA.ID_USUARIO%TYPE
) AS
BEGIN
    INSERT INTO VENTA (
        NUMERO,
        IMPUESTOS,
        ID_CLIENTE,
        ID_USUARIO
    ) VALUES (
        p_numero,
        p_impuestos,
        p_id_cliente,
        p_id_usuario
    );
END;
/

-- 39. PROCEDIMIENTO PARA INSERTAR UN DETALLE DE VENTA

CREATE OR REPLACE PROCEDURE PROC_insertar_venta_detalle (
    p_cantidad       IN VENTA_DETALLE.CANTIDAD%TYPE,
    p_precio_unitario IN VENTA_DETALLE.PRECIO_UNITARIO%TYPE,
    p_descuento      IN VENTA_DETALLE.DESCUENTO%TYPE,
    p_id_producto    IN VENTA_DETALLE.ID_PRODUCTO%TYPE,
    p_id_venta       IN VENTA_DETALLE.ID_VENTA%TYPE
) AS
BEGIN
    INSERT INTO VENTA_DETALLE (
        CANTIDAD,
        PRECIO_UNITARIO,
        DESCUENTO,
        ID_PRODUCTO,
        ID_VENTA
    ) VALUES (
        p_cantidad,
        p_precio_unitario,
        p_descuento,
        p_id_producto,
        p_id_venta
    );
END;
/

-- 40. PROCEDIMIENTO QUE DEVUELVE EL NUMERO MAYOR DE LAS VENTAS

CREATE OR REPLACE PROCEDURE PROC_OBTENER_MAX_NUMERO_VENTA (
    p_max_numero OUT VENTA.NUMERO%TYPE
) AS
BEGIN
    SELECT NVL(MAX(NUMERO), 0)
    INTO p_max_numero
    FROM VENTA;
END;
/

-- 41. Procedimiento que devuelve una venta a partir de un id

CREATE OR REPLACE PROCEDURE PROC_OBTENER_VENTA (
    p_id_venta   IN  VENTA.ID_VENTA%TYPE,
    p_numero     OUT VENTA.NUMERO%TYPE,
    p_impuestos  OUT VENTA.IMPUESTOS%TYPE,
    p_id_cliente OUT VENTA.ID_CLIENTE%TYPE,
    p_nombre_cliente OUT CLIENTE.NOMBRE_CLIENTE%TYPE
    
) AS
BEGIN
    SELECT v.NUMERO, v.IMPUESTOS, v.ID_CLIENTE, c.NOMBRE_CLIENTE
    INTO   p_numero, p_impuestos, p_id_cliente, p_nombre_cliente
    FROM   VENTA v
    JOIN    CLIENTE C on c.ID_CLIENTE = v.ID_CLIENTE
    WHERE  ID_VENTA = p_id_venta;
EXCEPTION
    WHEN NO_DATA_FOUND THEN
        p_numero     := NULL;
        p_impuestos  := NULL;
        p_id_cliente := NULL;
    WHEN OTHERS THEN
        RAISE;
END;
/

--42. procedimiento que devuelve el detalle de una venta

CREATE OR REPLACE PROCEDURE PROC_LISTAR_DETALLES_VENTA (
    p_id_venta IN VENTA_DETALLE.ID_VENTA%TYPE,
    p_cursor OUT SYS_REFCURSOR
)
AS
BEGIN
    OPEN p_cursor FOR
        SELECT 
            vd.ID_PRODUCTO,
            p.NOMBRE_PRODUCTO,
            vd.CANTIDAD,
            vd.DESCUENTO,
            vd.PRECIO_UNITARIO
            
        FROM VENTA_DETALLE vd
        JOIN PRODUCTO p ON vd.ID_PRODUCTO = p.ID_PRODUCTO
        WHERE vd.ID_VENTA = p_id_venta;
END;
/

--42. procedimiento que devuelve la ultima venta ingresada

CREATE OR REPLACE PROCEDURE PROC_OBTENER_ULTIMO_ID_VENTA (
    p_id_venta OUT NUMBER
) AS
BEGIN
    SELECT NVL(MAX(ID_VENTA), 1) INTO p_id_venta FROM VENTA;
END;
/

--43. procedimiento que optiene un producto a partir de un id

CREATE OR REPLACE PROCEDURE PROC_OBTENER_PRODUCTO (
    p_id_producto IN PRODUCTO.ID_PRODUCTO%TYPE,
    p_cursor      OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA
        FROM PRODUCTO
        WHERE ID_PRODUCTO = p_id_producto;
END;
/

--43. Procedimiento para obtener los usuarios por ID

CREATE OR REPLACE PROCEDURE PROC_obtener_usuario_por_id (
    p_id_usuario IN USUARIO.ID_USUARIO%TYPE,
    p_cursor OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT 
            NOMBRE_USUARIO,
            TELEFONO,
            CORREO,
            ROL,
            ESTADO
        FROM USUARIO
        WHERE ID_USUARIO = p_id_usuario;
END;
/

--44. Procedimiento para habilitar usuarios desactivados

CREATE OR REPLACE PROCEDURE PROC_habilitar_usuario (
    p_id IN USUARIO.ID_USUARIO%TYPE
) AS
BEGIN
    UPDATE USUARIO
    SET ESTADO = 1
    WHERE ID_USUARIO = p_id;
END;
/

--45. Procedimiento para obtener datos cliente por ID

CREATE OR REPLACE PROCEDURE PROC_OBTENER_CLIENTE (
  p_id_cliente IN CLIENTE.ID_CLIENTE%TYPE,
  p_nombre     OUT CLIENTE.NOMBRE_CLIENTE%TYPE,
  p_correo     OUT CLIENTE.CORREO%TYPE,
  p_tipo       OUT CLIENTE.ID_TIPO_CLINICA%TYPE
) AS
BEGIN
  SELECT NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA
    INTO p_nombre, p_correo, p_tipo
    FROM CLIENTE
   WHERE ID_CLIENTE = p_id_cliente;
END;
/

--46. Procedimiento para obtener telefonos de un cliente

CREATE OR REPLACE PROCEDURE PROC_OBTENER_TELEFONOS_CLIENTE (
  p_id_cliente IN TELEFONO_CLIENTE.ID_CLIENTE%TYPE,
  p_cursor     OUT SYS_REFCURSOR
) AS
BEGIN
  OPEN p_cursor FOR
    SELECT ID_TELEFONO, TELEFONO
      FROM TELEFONO_CLIENTE
     WHERE ID_CLIENTE = p_id_cliente;
END;
/

--47. Procedimiento para obtener solo los IDs de telï¿½fono de un cliente

CREATE OR REPLACE PROCEDURE PROC_OBTENER_ID_TELEFONOS_CLIENTE (
  p_id_cliente IN TELEFONO_CLIENTE.ID_CLIENTE%TYPE,
  p_cursor     OUT SYS_REFCURSOR
) AS
BEGIN
  OPEN p_cursor FOR
    SELECT ID_TELEFONO
      FROM TELEFONO_CLIENTE
     WHERE ID_CLIENTE = p_id_cliente;
END;
/

--48. Procedimiento para insertar un telefono cliente

CREATE OR REPLACE PROCEDURE PROC_insertar_telefono_cliente (
  p_id_cliente IN TELEFONO_CLIENTE.ID_CLIENTE%TYPE,
  p_telefono   IN TELEFONO_CLIENTE.TELEFONO%TYPE
) AS
BEGIN
  INSERT INTO TELEFONO_CLIENTE (ID_CLIENTE, TELEFONO)
  VALUES (p_id_cliente, p_telefono);
END;
/

--49. Procedimiento para actualizar un telefono cliente

CREATE OR REPLACE PROCEDURE PROC_actualizar_telefono_cliente (
  p_id_tel   IN TELEFONO_CLIENTE.ID_TELEFONO%TYPE,
  p_telefono IN TELEFONO_CLIENTE.TELEFONO%TYPE
) AS
BEGIN
  UPDATE TELEFONO_CLIENTE
     SET TELEFONO = p_telefono
   WHERE ID_TELEFONO = p_id_tel;
END;
/



--50. Procedimiento para eliminar un telefono cliente

CREATE OR REPLACE PROCEDURE PROC_eliminar_telefono_cliente (
  p_id_tel IN TELEFONO_CLIENTE.ID_TELEFONO%TYPE
) AS
BEGIN
  DELETE FROM TELEFONO_CLIENTE
   WHERE ID_TELEFONO = p_id_tel;
END;
/

--50. Procedimiento mostrar la bitacora

CREATE OR REPLACE PROCEDURE PROC_LISTAR_BITACORA(p_cursor OUT SYS_REFCURSOR) AS
BEGIN
  OPEN p_cursor FOR
    SELECT 
      b.ID_BITACORA,
      TO_CHAR(B.FECHA_OPERACION, 'DD-MM-YYYY HH24:MI:SS') AS FECHA_OPERACION,
      u.ID_USUARIO,
      u.NOMBRE_USUARIO,
      u.CORREO,
      b.DESCRIPCION
    FROM 
      BITACORA b
    LEFT JOIN 
      USUARIO u ON b.ID_USUARIO = u.ID_USUARIO
    ORDER BY 
      b.FECHA_OPERACION DESC;
END;
/

-- 51. PROCEDIMIENTO PARA HABILITAR - DESHABILITAR  UNA VENTA CAMBIANDO SU ESTADO A UNO O A CERO

CREATE OR REPLACE PROCEDURE PROC_habilitar_venta (
    p_id IN VENTA.ID_VENTA%TYPE,
    p_estado IN VENTA.ESTADO%TYPE
) AS
BEGIN
    UPDATE VENTA
    SET ESTADO = p_estado
    WHERE ID_VENTA = p_id;
END;
/




-- -------------------------- VISTAS ------------------------------------------------------

-- 1. Vista de Usuarios Deshabilitados

CREATE OR REPLACE VIEW VW_USUARIOS_DESHABILITADOS AS
SELECT 
    ID_USUARIO,
    NOMBRE_USUARIO,
    TELEFONO,
    CORREO,
    ROL,
    FECHA_REGISTRO
FROM 
    USUARIO
WHERE 
    ESTADO = 0;

-- 2. Vista de clientes filtrados por Tipo_Clinica

CREATE OR REPLACE VIEW VW_CLIENTES_CON_TIPO_CLINICA AS
SELECT
    C.ID_CLIENTE,
    C.NOMBRE_CLIENTE,
    C.CORREO,
    C.ID_TIPO_CLINICA,
    TC.DESCRIPCION AS TIPO_CLINICA
FROM
    CLIENTE C
JOIN
    TIPO_CLINICA TC ON C.ID_TIPO_CLINICA = TC.ID_TIPO_CLINICA;

-- 3. Vista de cantidad de compras efectuadas por cliente

CREATE OR REPLACE VIEW VW_CANTIDAD_VENTAS_POR_CLIENTE AS
SELECT
    C.ID_CLIENTE,
    C.NOMBRE_CLIENTE,
    COUNT(V.ID_VENTA) AS CANTIDAD_VENTAS
FROM
    CLIENTE C
JOIN
    VENTA V ON C.ID_CLIENTE = V.ID_CLIENTE
GROUP BY
    C.ID_CLIENTE,
    C.NOMBRE_CLIENTE
HAVING
    COUNT(V.ID_VENTA) >= 1
ORDER BY
    C.NOMBRE_CLIENTE;

-- 4. Vista de Proveedores Deshabilitados

CREATE OR REPLACE VIEW VW_PROVEEDORES_DESHABILITADOS AS
SELECT 
    p.ID_PROVEEDOR,
    p.NOMBRE_PROVEEDOR,
    p.CORREO,
    p.DIRECCION_PROVEEDOR,
    -- Agrupar telefonos en una sola columna
    (
        SELECT LISTAGG(t.TELEFONO, CHR(10)) WITHIN GROUP (ORDER BY t.ID_TELEFONO)
        FROM TELEFONO_PROVEEDOR t
        WHERE t.ID_PROVEEDOR = p.ID_PROVEEDOR
    ) AS TELEFONOS
FROM 
    PROVEEDOR p
WHERE 
    p.ESTADO = 0;

-- 5. VISTA PARA LOS 5 PRODUCTOS MENOS VENDIDOS

CREATE OR REPLACE VIEW VW_PRODUCTOS_MENOS_VENDIDOS AS
SELECT 
    p.ID_PRODUCTO,
    p.NOMBRE_PRODUCTO,
    c.NOMBRE_CATEGORIA,
    NVL(SUM(vd.CANTIDAD), 0) AS TOTAL_VENDIDO
FROM PRODUCTO p
LEFT JOIN VENTA_DETALLE vd ON p.ID_PRODUCTO = vd.ID_PRODUCTO
JOIN CATEGORIA c ON p.ID_CATEGORIA = c.ID_CATEGORIA
GROUP BY p.ID_PRODUCTO, p.NOMBRE_PRODUCTO, c.NOMBRE_CATEGORIA
ORDER BY TOTAL_VENDIDO ASC
FETCH FIRST 5 ROWS ONLY;

-- -------------------------- STORED PROCEDURES (Para Vistas) ------------------------------------------------------

--1. Procedimiento almacenado para recorrer la vista de usuarios deshabilitados

CREATE OR REPLACE PROCEDURE PROC_listar_usuarios_deshabilitados (
    p_cursor OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT ID_USUARIO, NOMBRE_USUARIO, TELEFONO, CORREO, ROL, FECHA_REGISTRO
        FROM VW_USUARIOS_DESHABILITADOS;
END;
/

--2. Procedimiento almacenado para recorrer la vista de los clientes por tipo clinica

CREATE OR REPLACE PROCEDURE PROC_obtener_clientes_por_clinica (
    p_id_tipo_clinica IN TIPO_CLINICA.ID_TIPO_CLINICA%TYPE,
    p_cursor OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT * 
        FROM VW_CLIENTES_CON_TIPO_CLINICA 
        WHERE ID_TIPO_CLINICA = p_id_tipo_clinica;
END;
/

-- 3. Procedimiento que manipula la vista de Proveedores Deshabilitados 

CREATE OR REPLACE PROCEDURE PROC_LISTAR_PROVEEDORES_DESHABILITADOS (
    p_cursor OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT ID_PROVEEDOR, NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, TELEFONOS
        FROM VW_PROVEEDORES_DESHABILITADOS;
END;
/

-- 4. Procedimiento para consultar lo productos menos vendidos

CREATE OR REPLACE PROCEDURE PROC_LISTAR_PRODUCTOS_MENOS_VENDIDOS (
    p_cursor OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT * FROM VW_PRODUCTOS_MENOS_VENDIDOS;
END;
/


-- -------------------------- CONTEXTOS ------------------------------------------------------
-----CTX_Usuario
CREATE OR REPLACE CONTEXT APP_CTX USING pkg_contexto_usuario;
/

CREATE OR REPLACE PACKAGE pkg_contexto_usuario AS
  PROCEDURE set_usuario(p_id_usuario IN NUMBER);
  PROCEDURE limpiar_usuario;
END;
/


-- -------------------------- PAQUETES ------------------------------------------------------

-----PKG_Usuario
CREATE OR REPLACE PACKAGE BODY pkg_contexto_usuario AS
  PROCEDURE set_usuario(p_id_usuario IN NUMBER) IS
  BEGIN
    DBMS_SESSION.SET_CONTEXT('APP_CTX', 'ID_USUARIO', TO_CHAR(p_id_usuario));
  END;

  PROCEDURE limpiar_usuario IS
  BEGIN
    DBMS_SESSION.CLEAR_CONTEXT('APP_CTX', 'ID_USUARIO');
  END;
END;
/

-- -------------------------- TRIGGER ------------------------------------------------------

-- 1. INSERTAR +506 al numero

CREATE OR REPLACE TRIGGER TRG_TELEFONO_FORMATO
BEFORE INSERT ON TELEFONO_PROVEEDOR
FOR EACH ROW
BEGIN
  IF SUBSTR(:NEW.TELEFONO, 1, 4) != '+506' THEN
    :NEW.TELEFONO := '+506 ' || :NEW.TELEFONO;
  END IF;
END;
/

-- 2. Auditar los cambios de la tabla producto en bitacora

CREATE OR REPLACE TRIGGER trg_bitacora_producto
AFTER INSERT OR UPDATE OR DELETE ON PRODUCTO
FOR EACH ROW
DECLARE
  v_usuario     NUMBER := TO_NUMBER(SYS_CONTEXT('APP_CTX', 'ID_USUARIO'));
  v_operacion   VARCHAR2(10);
  v_descripcion CLOB;
BEGIN
  IF INSERTING THEN
    v_operacion := 'INSERT';
    v_descripcion := 'Se insertï¿½ el producto: ' ||
                     'ID=' || :NEW.ID_PRODUCTO || ', ' ||
                     'NOMBRE=' || :NEW.NOMBRE_PRODUCTO || ', ' ||
                     'PRECIO=' || :NEW.PRECIO || ', ' ||
                     'ID_PROVEEDOR=' || :NEW.ID_PROVEEDOR || ', ' ||
                     'ID_CATEGORIA=' || :NEW.ID_CATEGORIA;

  ELSIF UPDATING THEN
    v_operacion := 'UPDATE';
    v_descripcion := 'Se actualizï¿½ el producto ID=' || :OLD.ID_PRODUCTO || ' ? ' ||
                     'ANTES [NOMBRE=' || :OLD.NOMBRE_PRODUCTO || ', PRECIO=' || :OLD.PRECIO || '] ? ' ||
                     'DESPUï¿½S [NOMBRE=' || :NEW.NOMBRE_PRODUCTO || ', PRECIO=' || :NEW.PRECIO || ']';

  ELSIF DELETING THEN
    v_operacion := 'DELETE';
    v_descripcion := 'Se eliminï¿½ el producto: ' ||
                     'ID=' || :OLD.ID_PRODUCTO || ', ' ||
                     'NOMBRE=' || :OLD.NOMBRE_PRODUCTO || ', ' ||
                     'PRECIO=' || :OLD.PRECIO;
  END IF;

  INSERT INTO BITACORA (
    ID_USUARIO,
    FECHA_OPERACION,
    DESCRIPCION
  ) VALUES (
    v_usuario,
    SYSTIMESTAMP,
    v_operacion || ' - ' || v_descripcion
  );
END;
/

-- 3. Auditar los cambios de la tabla venta en bitacora

CREATE OR REPLACE TRIGGER trg_bitacora_venta
AFTER INSERT OR UPDATE OR DELETE ON VENTA
FOR EACH ROW
DECLARE
  v_usuario     NUMBER := TO_NUMBER(SYS_CONTEXT('APP_CTX', 'ID_USUARIO'));
  v_operacion   VARCHAR2(10);
  v_descripcion CLOB;
BEGIN
  IF INSERTING THEN
    v_operacion := 'INSERT';
    v_descripcion := 'Se insertó la venta: ' ||
                     'ID=' || :NEW.ID_VENTA || ', ' ||
                     'NUMERO=' || :NEW.NUMERO || ', ' ||
                     'IMPUESTOS=' || :NEW.IMPUESTOS || ', ' ||
                     'ID_CLIENTE=' || :NEW.ID_CLIENTE || ', ' ||
                     'ID_USUARIO=' || :NEW.ID_USUARIO;

  ELSIF UPDATING THEN
    v_operacion := 'UPDATE';
    v_descripcion := 'Se actualizó la venta ID=' || :OLD.ID_VENTA || ' ? ' ||
                     'ANTES [NUMERO=' || :OLD.NUMERO || ', IMPUESTOS=' || :OLD.IMPUESTOS || '] ? ' ||
                     'DESPUÉS [NUMERO=' || :NEW.NUMERO || ', IMPUESTOS=' || :NEW.IMPUESTOS || ']';

  ELSIF DELETING THEN
    v_operacion := 'DELETE';
    v_descripcion := 'Se eliminó la venta: ' ||
                     'ID=' || :OLD.ID_VENTA || ', ' ||
                     'NUMERO=' || :OLD.NUMERO || ', ' ||
                     'IMPUESTOS=' || :OLD.IMPUESTOS;
  END IF;

  INSERT INTO BITACORA (
    ID_USUARIO,
    FECHA_OPERACION,
    DESCRIPCION
  ) VALUES (
    v_usuario,
    SYSTIMESTAMP,
    v_operacion || ' - ' || v_descripcion
  );
END;
/

-- 4. Auditar los cambios de la tabla venta_detalle en bitacora

CREATE OR REPLACE TRIGGER trg_bitacora_venta_detalle
AFTER INSERT OR UPDATE OR DELETE ON VENTA_DETALLE
FOR EACH ROW
DECLARE
  v_usuario     NUMBER := TO_NUMBER(SYS_CONTEXT('APP_CTX', 'ID_USUARIO'));
  v_operacion   VARCHAR2(10);
  v_descripcion CLOB;
BEGIN
  IF INSERTING THEN
    v_operacion := 'INSERT';
    v_descripcion := 'Se insertó el detalle de venta: ' ||
                     'ID=' || :NEW.ID_VENTA_DETALLE || ', ' ||
                     'ID_VENTA=' || :NEW.ID_VENTA || ', ' ||
                     'ID_PRODUCTO=' || :NEW.ID_PRODUCTO || ', ' ||
                     'CANTIDAD=' || :NEW.CANTIDAD || ', ' ||
                     'PRECIO_UNITARIO=' || :NEW.PRECIO_UNITARIO || ', ' ||
                     'DESCUENTO=' || :NEW.DESCUENTO;

  ELSIF UPDATING THEN
    v_operacion := 'UPDATE';
    v_descripcion := 'Se actualizó el detalle de venta ID=' || :OLD.ID_VENTA_DETALLE || ' ? ' ||
                     'ANTES [CANTIDAD=' || :OLD.CANTIDAD || ', PRECIO_UNITARIO=' || :OLD.PRECIO_UNITARIO || ', DESCUENTO=' || :OLD.DESCUENTO || '] ? ' ||
                     'DESPUÉS [CANTIDAD=' || :NEW.CANTIDAD || ', PRECIO_UNITARIO=' || :NEW.PRECIO_UNITARIO || ', DESCUENTO=' || :NEW.DESCUENTO || ']';

  ELSIF DELETING THEN
    v_operacion := 'DELETE';
    v_descripcion := 'Se eliminó el detalle de venta: ' ||
                     'ID=' || :OLD.ID_VENTA_DETALLE || ', ' ||
                     'ID_VENTA=' || :OLD.ID_VENTA || ', ' ||
                     'ID_PRODUCTO=' || :OLD.ID_PRODUCTO || ', ' ||
                     'CANTIDAD=' || :OLD.CANTIDAD || ', ' ||
                     'PRECIO_UNITARIO=' || :OLD.PRECIO_UNITARIO || ', ' ||
                     'DESCUENTO=' || :OLD.DESCUENTO;
  END IF;

  INSERT INTO BITACORA (
    ID_USUARIO,
    FECHA_OPERACION,
    DESCRIPCION
  ) VALUES (
    v_usuario,
    SYSTIMESTAMP,
    v_operacion || ' - ' || v_descripcion
  );
END;
/

-- 5. Auditar los cambios de la tabla usuarios en bitacora
CREATE OR REPLACE TRIGGER trg_bitacora_usuario
AFTER INSERT OR UPDATE OR DELETE ON USUARIO
FOR EACH ROW
DECLARE
  v_usuario     NUMBER := TO_NUMBER(SYS_CONTEXT('APP_CTX', 'ID_USUARIO'));
  v_operacion   VARCHAR2(10);
  v_descripcion CLOB;
BEGIN
  IF INSERTING THEN
    v_operacion := 'INSERT';
    v_descripcion := 'Se insertó el usuario: ' ||
                     'ID=' || :NEW.ID_USUARIO || ', ' ||
                     'NOMBRE=' || :NEW.NOMBRE_USUARIO || ', ' ||
                     'CORREO=' || :NEW.CORREO || ', ' ||
                     'ROL=' || :NEW.ROL;

  ELSIF UPDATING THEN
    v_operacion := 'UPDATE';
    v_descripcion := 'Se actualizó el usuario ID=' || :OLD.ID_USUARIO || ' ? ' ||
                     'ANTES [NOMBRE=' || :OLD.NOMBRE_USUARIO || ', CORREO=' || :OLD.CORREO || ', ROL=' || :OLD.ROL || '] ? ' ||
                     'DESPUÉS [NOMBRE=' || :NEW.NOMBRE_USUARIO || ', CORREO=' || :NEW.CORREO || ', ROL=' || :NEW.ROL || ']';

  ELSIF DELETING THEN
    v_operacion := 'DELETE';
    v_descripcion := 'Se eliminó el usuario: ' ||
                     'ID=' || :OLD.ID_USUARIO || ', ' ||
                     'NOMBRE=' || :OLD.NOMBRE_USUARIO || ', ' ||
                     'CORREO=' || :OLD.CORREO;
  END IF;

  INSERT INTO BITACORA (ID_USUARIO, FECHA_OPERACION, DESCRIPCION)
  VALUES (v_usuario, SYSTIMESTAMP, v_operacion || ' - ' || v_descripcion);
END;
/

--6. Auditar los cambios de la tabla cliente en bitacora
CREATE OR REPLACE TRIGGER trg_bitacora_cliente
AFTER INSERT OR UPDATE OR DELETE ON CLIENTE
FOR EACH ROW
DECLARE
  v_usuario     NUMBER := TO_NUMBER(SYS_CONTEXT('APP_CTX', 'ID_USUARIO'));
  v_operacion   VARCHAR2(10);
  v_descripcion CLOB;
BEGIN
  IF INSERTING THEN
    v_operacion := 'INSERT';
    v_descripcion := 'Se insertó el cliente: ' ||
                     'ID=' || :NEW.ID_CLIENTE || ', ' ||
                     'NOMBRE=' || :NEW.NOMBRE_CLIENTE || ', ' ||
                     'CORREO=' || :NEW.CORREO || ', ' ||
                     'TIPO_CLINICA=' || :NEW.ID_TIPO_CLINICA;

  ELSIF UPDATING THEN
    v_operacion := 'UPDATE';
    v_descripcion := 'Se actualizó el cliente ID=' || :OLD.ID_CLIENTE || ' ? ' ||
                     'ANTES [NOMBRE=' || :OLD.NOMBRE_CLIENTE || ', CORREO=' || :OLD.CORREO || ', TIPO=' || :OLD.ID_TIPO_CLINICA || '] ? ' ||
                     'DESPUÉS [NOMBRE=' || :NEW.NOMBRE_CLIENTE || ', CORREO=' || :NEW.CORREO || ', TIPO=' || :NEW.ID_TIPO_CLINICA || ']';

  ELSIF DELETING THEN
    v_operacion := 'DELETE';
    v_descripcion := 'Se eliminó el cliente: ' ||
                     'ID=' || :OLD.ID_CLIENTE || ', ' ||
                     'NOMBRE=' || :OLD.NOMBRE_CLIENTE || ', ' ||
                     'CORREO=' || :OLD.CORREO;
  END IF;

  INSERT INTO BITACORA (ID_USUARIO, FECHA_OPERACION, DESCRIPCION)
  VALUES (v_usuario, SYSTIMESTAMP, v_operacion || ' - ' || v_descripcion);
END;
/

--7. Auditar los cambios de la tabla tipo clinica en bitacora
CREATE OR REPLACE TRIGGER trg_bitacora_tipo_clinica
AFTER INSERT OR UPDATE OR DELETE ON TIPO_CLINICA
FOR EACH ROW
DECLARE
  v_usuario     NUMBER := TO_NUMBER(SYS_CONTEXT('APP_CTX', 'ID_USUARIO'));
  v_operacion   VARCHAR2(10);
  v_descripcion CLOB;
BEGIN
  IF INSERTING THEN
    v_operacion := 'INSERT';
    v_descripcion := 'Se insertó el tipo de clínica: ' ||
                     'ID=' || :NEW.ID_TIPO_CLINICA || ', ' ||
                     'DESCRIPCION=' || :NEW.DESCRIPCION;

  ELSIF UPDATING THEN
    v_operacion := 'UPDATE';
    v_descripcion := 'Se actualizó el tipo de clínica ID=' || :OLD.ID_TIPO_CLINICA || ' ? ' ||
                     'ANTES [DESCRIPCION=' || :OLD.DESCRIPCION || '] ? ' ||
                     'DESPUÉS [DESCRIPCION=' || :NEW.DESCRIPCION || ']';

  ELSIF DELETING THEN
    v_operacion := 'DELETE';
    v_descripcion := 'Se eliminó el tipo de clínica: ' ||
                     'ID=' || :OLD.ID_TIPO_CLINICA || ', ' ||
                     'DESCRIPCION=' || :OLD.DESCRIPCION;
  END IF;

  INSERT INTO BITACORA (ID_USUARIO, FECHA_OPERACION, DESCRIPCION)
  VALUES (v_usuario, SYSTIMESTAMP, v_operacion || ' - ' || v_descripcion);
END;
/

-- -------------------------- FUNCIONES ------------------------------------------------------

CREATE OR REPLACE FUNCTION FUNC_REPORTE_VENTAS_RANGO (
    p_inicio IN DATE,
    p_fin    IN DATE
) RETURN SYS_REFCURSOR
IS
    v_cursor SYS_REFCURSOR;
BEGIN
    OPEN v_cursor FOR
        SELECT ID_VENTA, NUMERO, FECHA, IMPUESTOS
        FROM VENTA
        WHERE FECHA BETWEEN p_inicio AND p_fin
          AND ESTADO = 1
        ORDER BY FECHA;
    RETURN v_cursor;
END;
/

CREATE OR REPLACE FUNCTION FUNC_DETALLE_VENTA (
    p_id_venta IN NUMBER
) RETURN SYS_REFCURSOR
IS
    v_cursor SYS_REFCURSOR;
BEGIN
    OPEN v_cursor FOR
        SELECT 
            P.NOMBRE_PRODUCTO,
            D.CANTIDAD,
            D.PRECIO_UNITARIO,
            D.DESCUENTO,
            (D.CANTIDAD * D.PRECIO_UNITARIO * (1 - D.DESCUENTO / 100)) AS TOTAL_PRODUCTO
        FROM VENTA_DETALLE D
        JOIN PRODUCTO P ON P.ID_PRODUCTO = D.ID_PRODUCTO
        WHERE D.ID_VENTA = p_id_venta;
    RETURN v_cursor;
END;
/



CREATE OR REPLACE FUNCTION FUNC_TOTALES_VENTA (
    p_id_venta IN NUMBER
) RETURN SYS_REFCURSOR
IS
    v_cursor SYS_REFCURSOR;
BEGIN
    OPEN v_cursor FOR
        SELECT
            SUM(D.CANTIDAD * D.PRECIO_UNITARIO) AS SUBTOTAL,
            SUM(D.CANTIDAD * D.PRECIO_UNITARIO * (D.DESCUENTO / 100)) AS DESCUENTO_TOTAL,
            SUM(D.CANTIDAD * D.PRECIO_UNITARIO * (1 - D.DESCUENTO / 100)) AS TOTAL
        FROM VENTA_DETALLE D
        WHERE D.ID_VENTA = p_id_venta;
    RETURN v_cursor;
END;
/

CREATE OR REPLACE FUNCTION FUNC_INFO_VENTA (
    p_id_venta IN NUMBER
) RETURN SYS_REFCURSOR
IS
    v_cursor SYS_REFCURSOR;
BEGIN
    OPEN v_cursor FOR
        SELECT 
            v.NUMERO,
            REGEXP_REPLACE(
                TO_CHAR(v.FECHA, 'DD-MM-YYYY HH24:MI'),
                '^([[:digit:]]{2})-([[:digit:]]{2})-([[:digit:]]{4}).*',
                '\1/\2/\3'
            ) AS FECHA,
            v.IMPUESTOS,
            c.NOMBRE_CLIENTE,
            c.CORREO AS CORREO_CLIENTE,
            (
                SELECT LISTAGG(t.TELEFONO, CHR(10)) WITHIN GROUP (ORDER BY t.ID_TELEFONO)
                FROM TELEFONO_CLIENTE t
                WHERE t.ID_CLIENTE = c.ID_CLIENTE
            ) AS TELEFONOS_CLIENTE,
            u.NOMBRE_USUARIO
        FROM VENTA v
        JOIN CLIENTE c ON v.ID_CLIENTE = c.ID_CLIENTE
        JOIN USUARIO u ON v.ID_USUARIO = u.ID_USUARIO
        WHERE v.ID_VENTA = p_id_venta;

    RETURN v_cursor;
END;
/

-- -------------------------- INSERCIÓN DE DATOS ------------------------------------------------------

-- Llamamos al procedimiento para crear la secuencia y trigger para las tablas
BEGIN
    PROC_CREAR_AUTOINCREMENTO('USUARIO', 'ID_USUARIO');
    PROC_CREAR_AUTOINCREMENTO('PRODUCTO', 'ID_PRODUCTO');
    PROC_CREAR_AUTOINCREMENTO('CATEGORIA', 'ID_CATEGORIA');
    PROC_CREAR_AUTOINCREMENTO('PROVEEDOR', 'ID_PROVEEDOR');
    PROC_CREAR_AUTOINCREMENTO('TELEFONO_PROVEEDOR', 'ID_TELEFONO');
    PROC_CREAR_AUTOINCREMENTO('TIPO_CLINICA', 'ID_TIPO_CLINICA');
    PROC_CREAR_AUTOINCREMENTO('VENTA', 'ID_VENTA');
    PROC_CREAR_AUTOINCREMENTO('VENTA_DETALLE', 'ID_VENTA_DETALLE');
    PROC_CREAR_AUTOINCREMENTO('BITACORA', 'ID_BITACORA');
    PROC_CREAR_AUTOINCREMENTO('TELEFONO_CLIENTE', 'ID_TELEFONO');
    PROC_CREAR_AUTOINCREMENTO('CLIENTE', 'ID_CLIENTE');
END;
/

-- -------------------------- INSERCIÓN DE USUARIOS ------------------------------------------------------
-- Insertamos datos en la tabla USUARIO
-- No se especifica ID_USUARIO porque se genera automï¿½ticamente por el trigger
-- La contraseï¿½a se guarda encriptada con HASH_PASSWORD
INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('admin', HASH_PASSWORD('a'), '88889999', 'admin@gmail.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('mgarcia', HASH_PASSWORD('abcd1234'), '88997766', 'mgarcia@hotmail.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('ccastro', HASH_PASSWORD('clave2023'), '88112233', 'ccastro@yahoo.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('nperez', HASH_PASSWORD('admin@456'), NULL, 'nperez@empresa.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('agomez', HASH_PASSWORD('securepass'), '87001122', 'agomez@correo.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('rsolis', HASH_PASSWORD('user789'), '87234567', 'rsolis@correo.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('zcarvajal', HASH_PASSWORD('pass0000'), '87654321', 'zcarvajal@empresa.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('fvalverde', HASH_PASSWORD('qwerty12'), NULL, 'fvalverde@hotmail.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('krojas', HASH_PASSWORD('mypass45'), '89998877', 'krojas@gmail.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('lcamacho', HASH_PASSWORD('admin2025'), '89887766', 'lcamacho@yahoo.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('vchacon', HASH_PASSWORD('val123'), '89112233', 'vchacon@mail.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('jcastillo', HASH_PASSWORD('clave999'), '89776655', 'jcastillo@correo.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('psegura', HASH_PASSWORD('qazwsx'), '88223344', 'psegura@empresa.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('mevargas', HASH_PASSWORD('456mypass'), '88118811', 'mevargas@gmail.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('darias', HASH_PASSWORD('nuevaclave'), '88334455', 'darias@hotmail.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('acalvo', HASH_PASSWORD('calvo123'), '87445566', 'acalvo@yahoo.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('nlopez', HASH_PASSWORD('lopez2024'), '87665544', 'nlopez@correo.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('fchacon', HASH_PASSWORD('ferclave'), '87990011', 'fchacon@medicos.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('ssoto', HASH_PASSWORD('soto1234'), '87223311', 'ssoto@empresa.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('yarias', HASH_PASSWORD('yariskey'), '87009988', 'yarias@mail.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('mmurillo', HASH_PASSWORD('mur123'), '87776655', 'mmurillo@correo.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('aaguilar', HASH_PASSWORD('agu789'), '88557744', 'aaguilar@gmail.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('lgranados', HASH_PASSWORD('gran9999'), '88443322', 'lgranados@empresa.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('kbrenes', HASH_PASSWORD('kbpass'), '88220011', 'kbrenes@yahoo.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('eduarte', HASH_PASSWORD('edu2022'), '88008800', 'eduarte@medmail.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('rramirez', HASH_PASSWORD('ramclave'), '87335544', 'rramirez@correo.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('hvalverde', HASH_PASSWORD('valkey22'), '87446677', 'hvalverde@gmail.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('ccampos', HASH_PASSWORD('campo123'), '87889911', 'ccampos@hotmail.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('mcordova', HASH_PASSWORD('cordpass'), '87112233', 'mcordova@yahoo.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('opadilla', HASH_PASSWORD('padilla'), '87004455', 'opadilla@mail.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('tvalle', HASH_PASSWORD('tvclave'), '88997755', 'tvalle@empresa.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('gquesada', HASH_PASSWORD('gq456'), '88224466', 'gquesada@correo.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('abola os', HASH_PASSWORD('abol789'), '88332211', 'abola os@gmail.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('ylizano', HASH_PASSWORD('lizanopass'), '87119933', 'ylizano@empresa.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('rmeza', HASH_PASSWORD('mezaadmin'), '87994400', 'rmeza@yahoo.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('kcalderon', HASH_PASSWORD('kcaldo22'), '88886644', 'kcalderon@gmail.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('fbrenes', HASH_PASSWORD('brenclave'), '88667788', 'fbrenes@hotmail.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('hvargas', HASH_PASSWORD('vargas2023'), '88009977', 'hvargas@correo.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('nviquez', HASH_PASSWORD('nvpass'), '88991100', 'nviquez@empresa.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('operez', HASH_PASSWORD('claveop'), '87774433', 'operez@mail.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('mleon', HASH_PASSWORD('ml1234'), '87663322', 'mleon@correo.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('rgranados', HASH_PASSWORD('rgranpass'), '88448877', 'rgranados@gmail.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('bsolis', HASH_PASSWORD('bsolclave'), '88221155', 'bsolis@empresa.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('ygarcia', HASH_PASSWORD('ygpass'), '88114488', 'ygarcia@yahoo.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('mcordero', HASH_PASSWORD('mcorclave'), '88992266', 'mcordero@mail.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('tquesada', HASH_PASSWORD('tq123'), '88773322', 'tquesada@gmail.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('cmejia', HASH_PASSWORD('cmeclave'), '88558899', 'cmejia@correo.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('iluna', HASH_PASSWORD('ilunakey'), '88665544', 'iluna@empresa.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('xcampos', HASH_PASSWORD('xcp2024'), '88007755', 'xcampos@gmail.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('jvargas', HASH_PASSWORD('jvclave'), '88889911', 'jvargas@correo.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('larias', HASH_PASSWORD('larpass'), '88330077', 'larias@yahoo.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('ecruz', HASH_PASSWORD('ecrpass'), '88445500', 'ecruz@empresa.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('cbenavides', HASH_PASSWORD('cbclave'), '88990077', 'cbenavides@mail.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('nbrenes', HASH_PASSWORD('npass'), '88661100', 'nbrenes@correo.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('uvargas', HASH_PASSWORD('uvclave'), '87775566', 'uvargas@gmail.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('dsanchez', HASH_PASSWORD('dspass'), '87557700', 'dsanchez@empresa.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('pmorales', HASH_PASSWORD('pmclave'), '88229911', 'pmorales@hotmail.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('lceballos', HASH_PASSWORD('lceclave'), '88441122', 'lceballos@yahoo.com', 2, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('zsolano', HASH_PASSWORD('zsolpass'), '88117744', 'zsolano@correo.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('emorales', HASH_PASSWORD('emorpass'), '88997744', 'emorales@empresa.com', 2, 1);

-- -------------------------- INSERCIÓN DE PROVEEDORES ------------------------------------------------------


INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ( 'Distribuidora Médica Central', 'contacto@dismedicentral.com', 'San José centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ( 'FarmaPro CR', 'info@farmaprocr.com', 'Heredia, San Pablo', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ( 'Salud Global', 'ventas@saludglobal.org', 'Alajuela, El Coyol', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ( 'Vital Logistics', 'logistica@vitallog.com', 'Cartago, Dulce Nombre', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ( 'Proveedora Vida', 'vida@proveedora.com', 'San Ramón centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ( 'Medicamentos Unidos', 'soporte@medunidos.com', 'Liberia, barrio La Cruz', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ( 'Insumos Vitales S.A.', 'ventas@insumosvitales.com', 'Desamparados, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ( 'Distribuidora Salus', 'salus@distsalus.com', 'Curridabat, Lomas de Ayarco', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ( 'PharmaTica', 'admin@pharmatica.com', 'Goicoechea, Guadalupe', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ( 'FarmaExpress', 'contacto@farmaexpress.com', 'Santo Domingo, Heredia', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ( 'Salud XXI', 'info@saludxxi.cr', 'Perez Zeledón, Daniel Flores', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ( 'Distribuidora Eterna', 'eterna@disterna.com', 'Nicoya, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ( 'Prosalud S.A.', 'ventas@prosalud.cr', 'Turrialba, La Suiza', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('TecnoSalud Global', 'info@tecnosaludglobal.com', 'San Carlos, Ciudad Quesada', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ( 'VitalCare', 'ventas@vitalcare.net', 'San Isidro, Pérez Zeledón', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ( 'FarmaLógica', 'soporte@farmalogica.com', 'Cartago, Occidental', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Grupo PharmaVida', 'contacto@pharmavida.com', 'San José, La Uruca', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('SaludTica', 'info@saludtica.org', 'Escazú, Trejos Montealegre', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('ProVida Central', 'admin@providacentral.com', 'Santa Ana, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('MediCR', 'ventas@medicr.com', 'Moravia, San Vicente', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Insumos Médicos JJ', 'jj@insumed.com', 'Orotina, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('FarmaPlus', 'soporte@farmaplus.com', 'Belén, Cariari', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Droguería Vital', 'ventas@drogvital.com', 'Vásquez de Coronado', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('RedSalud', 'red@salud.com', 'San José, Zapote', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Distribuciones KOS', 'info@kos.com', 'Alajuela, San Rafael', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Grupo FarmaRed', 'contacto@farmared.net', 'Puriscal centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('MediLink S.A.', 'soporte@medilink.com', 'Aserrí, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('VitalTech', 'info@vitaltech.org', 'Cañas, Guanacaste', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Eficiencia Médica', 'eficiencia@medica.cr', 'Naranjo, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('MedicSuministros', 'ventas@medicsuministros.com', 'Palmares, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Red Vital CR', 'redvital@costarica.com', 'Grecia, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Salud Moderna', 'modernasalud@correo.com', 'Tibás, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Farmex CR', 'admin@farmex.com', 'San Rafael de Heredia', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Suministros Omega', 'omega@suministros.net', 'Quepos, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Biotech Proveedores', 'ventas@biotech.cr', 'Barva, Heredia', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Insumédica', 'contacto@insumedica.com', 'La Unión, Tres Ríos', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('MediPlus Internacional', 'info@mediplus.com', 'Zarcero, Alajuela', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('FarmaStore', 'ventas@farmastore.com', 'Poás, Alajuela', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('VitaRed CR', 'vitar@correo.com', 'Siquirres, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('TecniFarma', 'admin@tecnifarma.com', 'Tilarán, Guanacaste', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Laboratorios VZ', 'labvz@correo.com', 'Bagaces, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Prosalud Central', 'contacto@prosalud.net', 'Esparza, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('BioVida', 'info@biovida.com', 'Guácimo, Limón', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('FarmaJJ', 'farmajj@ventas.com', 'Santa Cruz, Guanacaste', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('MediContacto', 'medicontacto@correo.com', 'Upala, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Salud Industrial', 'industrial@salud.com', 'Coto Brus, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Grupo Vital América', 'america@vital.com', 'Osa, Palmar Norte', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('FarmaOccidente', 'ventas@farmaoc.com', 'Buenos Aires, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Insumos Sanos', 'insan@ventas.com', 'Guatuso, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Distribuciones MegaSalud', 'mega@salud.com', 'Los Chiles, frontera', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Nova Salud', 'nova@saludnova.com', 'Parrita, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('FarmaLink', 'flink@ventas.com', 'Jiménez, Guápiles', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Ecosalud', 'eco@saludverde.com', 'Matina, Limón', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('TecnoInsumos', 'tecno@insumos.com', 'Talamanca, Bribrí', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('MedExpress', 'exp@medexpress.com', 'Turrubares, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('VitalSan', 'vsan@correo.com', 'Montes de Oro, Miramar', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Insumos del Sur', 'sur@insumos.com', 'Golfito, zona sur', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('CR Medical Supply', 'supply@crmedical.com', 'San Mateo, Alajuela', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('SaniTech', 'contacto@sanitech.com', 'Pococí, centro', SYSDATE);
  INSERT INTO PROVEEDOR ( NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, FECHA_REGISTRO)
  VALUES ('Grupo BioInsumos', 'bio@grupoinsumos.com', 'Limón centro', SYSDATE);


-- -------------------------- INSERCION DE TELEFONOS DE PROVEEDORES ------------------------------------------------------


INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001101', 1);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001102', 2);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001103', 3);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001104', 4);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001105', 5);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001106', 6);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001107', 7);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001108', 8);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001109', 9);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001110', 10);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001111', 11);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001112', 12);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001113', 13);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001114', 14);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001115', 15);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001116', 16);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001117', 17);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001118', 18);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001119', 19);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001120', 20);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001121', 21);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001122', 22);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001123', 23);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001124', 24);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001125', 25);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001126', 26);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001127', 27);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001128', 28);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001129', 29);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001130', 30);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001131', 31);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001132', 32);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001133', 33);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001134', 34);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001135', 35);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001136', 36);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001137', 37);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001138', 38);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001139', 39);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001140', 40);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001141', 41);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001142', 42);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001143', 43);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001144', 44);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001145', 45);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001146', 46);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001147', 47);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001148', 48);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001149', 49);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001150', 50);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001151', 51);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001152', 52);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001153', 53);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001154', 54);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001155', 55);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001156', 56);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001157', 57);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001158', 58);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001159', 59);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR) VALUES ( '22001160', 60);

-- -------------------------- INSERCION DE TIPOS DE CLINICA ------------------------------------------------------

INSERT INTO TIPO_CLINIC (DESCRIPCION) VALUES ('Cl nica General');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ('Cl nica Odontol gica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ('Cl nica Pedi trica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ('Cl nica Ginecol gica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ('Cl nica Dermatol gica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ('Cl nica Oftalmol gica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ('Cl nica Cardiol gica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ('Cl nica Urol gica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ('Cl nica Neurol gica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ('Cl nica Reumatol gica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ('Cl nica Ortop dica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ('Cl nica Gastroenterol gica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ('Cl nica Nefrol gica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ('Cl nica Oncol gica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ('Cl nica Endocrinol gica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ('Cl nica Otorrinolaringol gica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ('Cl nica Psicolog a Adultos');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ('Cl nica Psicolog a Infantil');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Medicina Interna');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Alergias');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Control de Peso');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Fertilidad');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Rehabilitaci n F sica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Medicina Deportiva');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Medicina Est tica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Nutrici n');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Salud Ocupacional');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Geriatr a');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Infectolog a');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Cirug a Pl stica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Cirug a General');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Neumolog a');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ('Cl nica de Hematolog a');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ('Cl nica de Proctolog a');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Podolog a');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Cuidados Paliativos');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Medicina Natural');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Homeopat a');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Acupuntura');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Medicina Alternativa');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Terapia F sica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Terapia del Lenguaje');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Terapia Ocupacional');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Psicopedagog a');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Audiolog a');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Logopedia');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Gen tica M dica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Psiquiatr a');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Neurodesarrollo');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Medicina del Sue o');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Medicina Nuclear');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Radiolog a');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Imagen Diagn stica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Patolog a Cl nica');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Biolog a Molecular');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Terapia Intensiva');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Urgencias M dicas');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Prevenci n del C ncer');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Control de Diabetes');
INSERT INTO TIPO_CLINICA (DESCRIPCION) VALUES ( 'Cl nica de Vacunaci n');



-- -------------------------- INSERCION DE CLIENTES ------------------------------------------------------

INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Carlos M ndez', 'carlos.mendez@mail.com', 1);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Ana Rodr guez', 'ana.rodri@gmail.com', 2);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Luis Solano', 'luissolano@yahoo.com', 3);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Mar a Vargas', 'mvargas@salud.com', 4);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Esteban Jim nez', 'esteban.jim@correo.com', 5);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Tatiana Chaves', 'tchaves@gmail.com', 6);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Fernando Castro', 'fcastro@hotmail.com', 7);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Karla Morales', 'kmorales@empresa.com', 8);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'David Rojas', 'drojas@gmail.com', 9);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Luc a C spedes', 'lucia.cesp@gmail.com', 10);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Diego Barrantes', 'dbarrantes@correo.com', 11);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Daniela Hern ndez', 'daniela.hdz@gmail.com', 12);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Jorge Uma a', 'juma a@hotmail.com', 13);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Mariela Vargas', 'mvargas@hotmail.com', 14);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Cristina L pez', 'clopez@medicos.cr', 15);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Andr s Quesada', 'aquesada@gmail.com', 16);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Andrea Navarro', 'anavarro@correo.com', 17);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Jos  Araya', 'jaraya@mail.com', 18);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Pamela Guti rrez', 'pgutierrez@gmail.com', 19);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Alejandro Campos', 'acampos@empresa.com',20);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Gabriela Salas', 'gsalas@hotmail.com', 21);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Roberto Mora', 'rmora@correo.com',22 );
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Allan Blanco', 'ablanco@medicos.cr', 23);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Vanessa P rez', 'vperez@gmail.com', 24);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Juan Jos  Vargas', 'juanjvargas@mail.com',25 );
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Andrea Castillo', 'acastillo@hotmail.com', 26);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Estefan a Gonz lez', 'egonzalez@correo.com', 27);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Gerson Mar n', 'gmarin@salud.com', 28);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Floribeth Z  iga', 'fzuniga@gmail.com', 29);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Camila Esquivel', 'cesquivel@empresa.com', 30);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Ra l Ram rez', 'rramirez@medico.com', 31);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Nathalia Picado', 'npicado@mail.com', 32);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Bryan C spedes', 'bcespedes@gmail.com', 33);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Joselyn Soto', 'jsoto@hotmail.com', 34);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Kevin Herrera', 'kherrera@correo.com', 35);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Diana Mora', 'dmora@medicos.cr', 36);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ('Carlos Murillo', 'cmurillo@empresa.com', 37);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Monserrat Ruiz', 'mruiz@gmail.com', 38);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Julio Corrales', 'jcorra@hotmail.com', 39);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Angie Brenes', 'abrenes@mail.com', 40);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Ricardo Calder n', 'rcalderon@gmail.com', 41);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Yolanda Segura', 'ysegura@correo.com', 42);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'El as Obando', 'eobando@hotmail.com', 43);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Natalie Rojas', 'nrojas@medicos.cr', 44);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Pedro Leiva', 'pleiva@correo.com', 45);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Gabriela Vega', 'gvega@gmail.com', 46);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Michelle L pez', 'mlopez@hotmail.com', 47);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Alonso Alvarado', 'aalvarado@empresa.com', 48);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Sof a Z  iga', 'szuniga@medicos.com', 49);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Jimena Blanco', 'jblanco@medicos.com', 60);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Andrey Cruz', 'acruz@correo.com', 50);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Juli n Mora', 'jmora@salud.com', 52);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Fiorella Gonz lez', 'fgonzalez@correo.com', 53);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'M nica Salazar', 'msalazar@hotmail.com', 54);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Tom s C spedes', 'tcespedes@mail.com', 55);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Karen Villalobos', 'kvillalobos@gmail.com', 56);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Carlos Alfaro', 'calfaro@empresa.com', 57);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Zuleima M ndez', 'zmendez@correo.com', 58);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Oscar Rodr guez', 'orodriguez@gmail.com', 59);
INSERT INTO CLIENTE  (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)  VALUES ( 'Dafne Quesada', 'dquesada@gmail.com', 51);

-- -------------------------- INSERCION DE CLIENTES ------------------------------------------------------

INSERT INTO TELEFONO_CLIENTE  (TELEFONO, ID_CLIENTE) VALUES ( '22001101', 1);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001102', 2);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001103', 3);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001104', 4);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001105', 5);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001106', 6);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ('22001107', 7);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001108', 8);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001109', 9);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001110', 10);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001111', 11);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001112', 12);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001113', 13);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001114', 14);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001115', 15);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001116', 16);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001117', 17);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001118', 18);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001119', 19);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001120', 20);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001121', 21);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001122', 22);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001123', 23);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001124', 24);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001125', 25);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001126', 26);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001127', 27);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001128', 28);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001129', 29);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001130', 30);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001131', 31);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001132', 32);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001133', 33);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001134', 34);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001135', 35);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001136', 36);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001137', 37);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001138', 38);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001139', 39);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001140', 40);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001141', 41);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001142', 42);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001143', 43);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001144', 44);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001145', 45);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001146', 46);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001147', 47);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001148', 48);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001149', 49);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001150', 50);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001151', 51);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001152', 52);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001153', 53);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001154', 54);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001155', 55);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001156', 56);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001157', 57);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001158', 58);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001159', 59);
INSERT INTO TELEFONO_CLIENTE (TELEFONO, ID_CLIENTE)  VALUES ( '22001160', 60);


-- -------------------------- INSERCION DE CATEGORIA ------------------------------------------------------

INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Medicamentos Gen ricos');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Material Descartable');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Equipos M dicos');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Reactivos de Laboratorio');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Vitaminas y Suplementos');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Instrumental Quir rgico');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ('Productos de Desinfecci n');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Soluciones Inyectables');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Antibi ticos');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Analg sicos');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Antiinflamatorios');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ('Antis pticos');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Jeringas y Agujas');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Tensiometros');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Gluc metros');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Ox metros de Pulso');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Productos Pedi tricos');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Anticonceptivos');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Pruebas R pidas');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Curaciones y Vendajes');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Vitaminas Naturales');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Suplementos Deportivos');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Desinfectantes de Superficies');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ('Alcohol y Gel Antibacterial');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Sueros Orales');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Guantes Quir rgicos');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Batas Desechables');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Mascarillas Faciales');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Equipos de Diagn stico');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Balanzas M dicas');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Camillas');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Sillas de Ruedas');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Muletas');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Term metros Digitales');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Tiras Reactivas');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Pr tesis Dentales');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Equipos de Ultrasonido');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Monitores de Signos Vitales');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Desfibriladores');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Nebulizadores');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Glucometr a');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Terapias Respiratorias');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Diagn stico COVID-19');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Paracetamol y Derivados');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Ibuprofeno y Derivados');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Preservativos');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Gasas y Algod n');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Cintas M dicas');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Botiquines de Emergencia');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Enzimas Digestivas');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Antivirales');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'F rmulas Pedi tricas');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Pruebas de Embarazo');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Diagn stico de Glicemia');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Terapias Intravenosas');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Vacunas');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Oxigenoterapia');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Cat teres y Accesorios');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Filtros para Ventilaci n');
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA) VALUES ( 'Dermatolog a Cl nica');


-- -------------------------- INSERCION DE PRODUCTOS ------------------------------------------------------


INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Paracetamol 500mg', 1200,  1, 1);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Guantes de l tex Talla M', 750,  2, 2);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Tensiometro digital', 22500,  3, 3);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Reactivo Hematolog a', 9800,  4, 4);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Vitamina C 1000mg', 3200,  5, 5);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Pinza quir rgica curva', 4100,  6, 6);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Alcohol 70% 1L', 1100,  7, 7);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Soluci n salina 500ml', 1600,  8, 8);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Amoxicilina 500mg', 1400,  9, 9);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Ibuprofeno 400mg', 1300,  10, 10);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Diclofenaco gel 30g', 1800,  11, 11);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Yodo Povidona 100ml', 950,  12, 12);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Jeringa de 5ml con aguja', 350,  13, 13);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Tensiometro aneroide', 18500,  14, 14);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Gluc metro Accu-Check', 29500,  15, 15);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Ox metro de dedo', 15900,  16, 16);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Crema para pa alitis', 1800,  17, 17);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'P ldoras anticonceptivas', 3500,  18, 18);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Prueba r pida COVID-19', 4800,  19, 19);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Vendas el sticas', 700,  20, 20);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Multivitam nico natural', 4100,  21, 21);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Prote na whey 2kg', 29000,  22, 22);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Lysol aerosol', 2900,  23, 23);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Gel antibacterial 250ml', 1300,  24, 24);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Suero oral sabor fresa', 1700,  25, 25);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ('Guantes est riles', 950,  26, 26);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Bata desechable manga larga', 1800,  27, 27);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Mascarilla quir rgica 3 capas', 500,  28, 28);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ('Estetoscopio doble campana', 21000,  29, 29);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'B scula digital m dica', 44000,  30, 30);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Camilla plegable', 37000,  31, 31);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Silla de ruedas est ndar', 44000,  32, 32);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Par de muletas ajustables', 28000,  33, 33);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Term metro infrarrojo', 15000,  34, 34);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Tiras reactivas glucosa (50u)', 7600,  35, 35);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Pr tesis parcial removible', 38000,  36, 36);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Ultrasonido port til', 445000,  37, 37);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Monitor multipar metro', 165000,  38, 38);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Desfibrilador semiautom tico', 320000,  39, 39);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Nebulizador con mascarilla', 18000,  40, 40);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Glucometro con kit b sico', 30000,  41, 41);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Equipo de terapia respiratoria', 12000,  42, 42);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Kit de prueba r pida COVID', 4600,  43, 43);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Paracetamol jarabe pedi trico', 1800,  44, 44);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Ibuprofeno suspensi n', 2400,  45, 45);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Preservativos 3 unidades', 950,  46, 46);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Gasa est ril 10x10cm', 350,  47, 47);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Cinta m dica transpirable', 700,  48, 48);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Botiqu n b sico port til', 8500,  49, 49);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Enzimas digestivas 60u', 5500,  50, 50);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Oseltamivir 75mg', 18500,  51, 51);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'F rmula infantil en polvo', 11500,  52, 52);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Prueba casera de embarazo', 1100,  53, 53);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Glucometro kit diagn stico', 32000,  54, 54);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Cat ter IV calibre 22', 450,  55, 55);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Vacuna Influenza tetravalente', 12500,  56, 56);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Mascarilla de ox geno con tubo', 2600,  57, 57);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Cat ter de aspiraci n 10Fr', 1100,  58, 58);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Filtro para ventilador mec nico', 6500,  59, 59);
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)  VALUES ( 'Crema dermatol gica antibacteriana', 4900,  60, 60);


-- -------------------------- INSERCION DE  VENTAS ------------------------------------------------------


INSERT INTO VENTA (NUMERO, FECHA, IMPUESTOS, ID_CLIENTE, ID_USUARIO)
VALUES (1, SYSDATE, 13, 1, 1);

INSERT INTO VENTA (NUMERO, FECHA, IMPUESTOS, ID_CLIENTE, ID_USUARIO)
VALUES (2, SYSDATE, 20, 1, 1);

INSERT INTO VENTA ( NUMERO, FECHA, IMPUESTOS, ID_CLIENTE, ID_USUARIO)
VALUES (3, SYSDATE, 10, 1, 2);

commit;


