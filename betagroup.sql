-- OJO: Eliminacion de datos de BETAGROUP desde ADMIN SYS
DROP USER betagroup CASCADE;
DROP TABLESPACE tbs_betagroup INCLUDING CONTENTS AND DATAFILES;

-- EJECUTAR COMO ADMIN_SYS
CREATE USER betagroup IDENTIFIED BY beta123;

GRANT CONNECT, RESOURCE TO betagroup;
GRANT UNLIMITED TABLESPACE TO betagroup;

-- Permisos para programaciï¿½n PL/SQL
GRANT CREATE PROCEDURE TO betagroup;
GRANT CREATE TRIGGER TO betagroup;
GRANT CREATE SEQUENCE TO betagroup;
GRANT CREATE VIEW TO betagroup;
GRANT CREATE ANY CONTEXT TO betagroup;
GRANT EXECUTE ON DBMS_SESSION TO BETAGROUP;


-- Permiso para usar funciones criptograficas
GRANT EXECUTE ON DBMS_CRYPTO TO betagroup;

/*Crear un nuevo tablespace*/

CREATE TABLESPACE tbs_betagroup
    DATAFILE 'C:\Oracle\oradata\ORCL\tbs_betagroup.dbf' SIZE 50M
    AUTOEXTEND ON NEXT 10M;

ALTER USER betagroup
    DEFAULT TABLESPACE tbs_betagroup;

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
    -- Agrupar telï¿½fonos en una sola columna
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

-- -------------------------- DATOS Y PRUEBAS ------------------------------------------------------

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


-- Insertamos datos en la tabla USUARIO
-- No se especifica ID_USUARIO porque se genera automï¿½ticamente por el trigger
-- La contraseï¿½a se guarda encriptada con HASH_PASSWORD

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO) 
VALUES ('admin', HASH_PASSWORD('a'), '89802238', 'admin@gmail.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('Vendedor 1', HASH_PASSWORD('a'), '89802238', 'vendedor@gmail.com', 0, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('Vendedor 2', HASH_PASSWORD('a'), '89802238', 'vendedor2@gmail.com', 0, 0);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO) 
VALUES ('admin 2', HASH_PASSWORD('a'), '89802238', 'admin2@gmail.com', 1, 0);

-- Mostramos los usuarios insertados (veremos el hash, no la contraseï¿½a original)
SELECT NOMBRE_USUARIO, CONTRASENA FROM USUARIO;

--Insertar una clinica
INSERT INTO TIPO_CLINICA (DESCRIPCION)
VALUES ('Clinica General');

--Insertar un cliente
INSERT INTO CLIENTE (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)
VALUES ('Maria Jimenez', 'maria.jimenez@gmail.com', 1);

INSERT INTO CLIENTE (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)
VALUES ('Michael Jordan', 'michael.jordan@gmail.com', 1);

-- 1. Insertar proveedores correctamente (incluye direcciï¿½n)
INSERT INTO PROVEEDOR (NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, ESTADO)
VALUES ('BeautyTech S.A.', 'ventas@beautytech.com', 'San Josï¿½, Costa Rica', 1);

INSERT INTO PROVEEDOR (NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, ESTADO)
VALUES ('Dermalaser CR', 'info@dermalaser.cr', 'Escazï¿½, Costa Rica', 1);

INSERT INTO PROVEEDOR (NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, ESTADO)
VALUES ('DermaSkin', 'contacto@dermaskin.com', 'Santa Ana, Costa Rica', 1);

INSERT INTO PROVEEDOR (NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, ESTADO)
VALUES ('Estetica Pura Vida', 'contacto@esteticapuravida.cr', 'Heredia, Costa Rica', 1);

INSERT INTO PROVEEDOR (NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, ESTADO)
VALUES ('MedEquipos CR', 'ventas@medequiposcr.com', 'Alajuela, Costa Rica', 0);

INSERT INTO PROVEEDOR (NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, ESTADO)
VALUES ('LaserClinic S.A.', 'info@laserclinic.cr', 'Cartago, Costa Rica', 1);

INSERT INTO PROVEEDOR (NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, ESTADO)
VALUES ('Tecnobeauty', 'servicio@tecnobeauty.com', 'San Pedro, Costa Rica', 0);

INSERT INTO PROVEEDOR (NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, ESTADO)
VALUES ('CosmoEsthetics', 'soporte@cosmoesthetics.com', 'Liberia, Costa Rica', 1);

-- 2. Insertar telï¿½fonos (ya existen los proveedores)
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('89842738', 1);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('88883344', 1);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('88888784', 1);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('72119988', 2);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('88991122', 3);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('88776655', 4);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('88882211', 5);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('89997744', 6);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('87778899', 7);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('85001133', 8);


-- 3. Insertar categoria
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA)
VALUES ('Equipos Esteticos');

-- 4. Insertar productos (ya existen proveedor y categorï¿½a)
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)
VALUES ('Soprano Titanium (Depilacion Lï¿½ser)', 500000, 1, 1);

INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)
VALUES ('Multifuncional 6 en 1 con Lipolaser 850mz y EMS', 1200000, 2, 1);

INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)
VALUES ('Lampara de cirugia', 800000, 3, 1);

INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)
VALUES ('Laser CO2', 1500000, 2, 1);

INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)
VALUES ('Laser de 4 longitudes soprano ', 1000000, 2, 1);


-- Ver procedimiento productos
VARIABLE rc REFCURSOR;
EXEC PROC_LISTAR_PRODUCTOS(:rc);
PRINT rc;

INSERT INTO VENTA (NUMERO, FECHA, IMPUESTOS, ID_CLIENTE, ID_USUARIO)
VALUES (1001, SYSDATE, 13, 1, 1);

INSERT INTO VENTA (NUMERO, FECHA, IMPUESTOS, ID_CLIENTE, ID_USUARIO)
VALUES (1002, SYSDATE, 20, 1, 1);

INSERT INTO VENTA ( NUMERO, FECHA, IMPUESTOS, ID_CLIENTE, ID_USUARIO)
VALUES ( 1003, SYSDATE, 10, 1, 2);

commit;

-- Drops de los nombres anteriores
-- Procedimientos
/*
DROP PROCEDURE VALIDAR_LOGIN;
DROP PROCEDURE LISTAR_USUARIOS;
DROP PROCEDURE CREAR_AUTOINCREMENTO;
DROP PROCEDURE insertar_usuario;
DROP PROCEDURE actualizar_usuario;
DROP PROCEDURE actualizar_usuario_sc;
DROP PROCEDURE eliminar_usuario;
DROP PROCEDURE insertar_cliente;
DROP PROCEDURE actualizar_cliente;
DROP PROCEDURE eliminar_cliente;
DROP PROCEDURE listar_clientes;
DROP PROCEDURE insertar_tipo_clinica;
DROP PROCEDURE actualizar_tipo_clinica;
DROP PROCEDURE eliminar_tipo_clinica;
DROP PROCEDURE listar_tipos_clinica;
DROP PROCEDURE LISTAR_PRODUCTOS;
DROP PROCEDURE insertar_producto;
DROP PROCEDURE actualizar_producto;
DROP PROCEDURE eliminar_producto;
DROP PROCEDURE LISTAR_CATEGORIAS;
DROP PROCEDURE insertar_categoria;
DROP PROCEDURE eliminar_categoria;
DROP PROCEDURE LISTAR_PROVEEDORES;
DROP PROCEDURE insertar_proveedor;
DROP PROCEDURE actualizar_proveedor;
DROP PROCEDURE eliminar_proveedor;
DROP PROCEDURE habilitar_proveedor;
DROP PROCEDURE OBTENER_PROVEEDOR;
DROP PROCEDURE OBTENER_ID_TELEFONOS;
DROP PROCEDURE OBTENER_TELEFONOS_PROVEEDOR;
DROP PROCEDURE actualizar_telefono_proveedor;
DROP PROCEDURE eliminar_telefono;
DROP PROCEDURE insertar_telefono_proveedor;
DROP PROCEDURE eliminar_venta;
DROP PROCEDURE eliminar_venta_detalle;
DROP PROCEDURE LISTAR_VENTAS;
DROP PROCEDURE insertar_venta;
DROP PROCEDURE insertar_venta_detalle;
DROP PROCEDURE OBTENER_MAX_NUMERO_VENTA;
DROP PROCEDURE OBTENER_VENTA;
DROP PROCEDURE LISTAR_DETALLES_VENTA;
DROP PROCEDURE OBTENER_ULTIMO_ID_VENTA;
DROP PROCEDURE OBTENER_PRODUCTO;
DROP PROCEDURE obtener_usuario_por_id;
DROP PROCEDURE habilitar_usuario;
DROP PROCEDURE OBTENER_CLIENTE;
DROP PROCEDURE OBTENER_TELEFONOS_CLIENTE;
DROP PROCEDURE OBTENER_ID_TELEFONOS_CLIENTE;
DROP PROCEDURE insertar_telefono_cliente;
DROP PROCEDURE actualizar_telefono_cliente;
DROP PROCEDURE eliminar_telefono_cliente;
DROP PROCEDURE LISTAR_BITACORA;

--Vistas
DROP VIEW V_USUARIOS_DESHABILITADOS;
DROP VIEW V_CLIENTES_CON_TIPO_CLINICA;
DROP VIEW V_PROVEEDORES_DESHABILITADOS;
DROP VIEW V_CANTIDAD_VENTAS_POR_CLIENTE;
DROP VIEW VISTA_PRODUCTOS_MENOS_VENDIDOS;

--Procedimientos dependientes de las vistas
DROP PROCEDURE listar_usuarios_deshabilitados;
DROP PROCEDURE obtener_clientes_por_clinica;
DROP PROCEDURE LISTAR_PROVEEDORES_DESHABILITADOS;
DROP PROCEDURE LISTAR_PRODUCTOS_MENOS_VENDIDOS;
*/

/*
GRANT CREATE ANY CONTEXT TO BETAGROUP;
GRANT EXECUTE ON SYS.DBMS_SESSION TO BETAGROUP;
*/
