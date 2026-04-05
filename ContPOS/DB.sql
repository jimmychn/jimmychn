SELECT * INTO ContPOS.dbo.ZIP FROM CONT.dbo.ZIP;
SELECT * INTO ContPOS.dbo.T_CODE FROM CONT.dbo.TAB_D WHERE T_NO='OCCUP';

CREATE TABLE ZIP(
	ZIP   varchar(10) NOT NULL PRIMARY KEY, -- 郵遞區號
	CITY nvarchar(20) NOT NULL,				-- 縣市
	AREA nvarchar(20) NOT NULL				-- 行政區域
);

CREATE TABLE TAB ( 
    T_NO VARCHAR(20), 
    TD_NO VARCHAR(20), 
    TD_NAME NVARCHAR(30), 
    SEQ INT DEFAULT '(0)', 
    PRIMARY KEY (T_NO,TD_NO)
);

CREATE TABLE Stores (
    StoreID CHAR(3) PRIMARY KEY,       	-- 門市代號 (A00, A01, A02, A03...)
    StoreName NVARCHAR(100) NOT NULL,   -- 門市名稱
	TAXID VARCHAR(20),					-- 統一編號
	TITLE VARCHAR(80),					-- 公司抬頭
    ZIPCode VARCHAR(20),             	-- 郵遞區號
	CITY VARCHAR(20),					-- 縣市	
	AREA VARCHAR(20),					-- 行政區域
	Address VARCHAR(200),				-- 詳細地址
	ManagerID VARCHAR(8),				-- 經理人	
    TEL VARCHAR(20),                   	-- 電話
    FAX VARCHAR(20),                   	-- 傳真
    IsActive BIT DEFAULT 1             	-- 是否啟用
);

-- 建立測試門市資料
INSERT INTO Stores (StoreID, StoreName, TAXID, TITLE, ZIPCode, CITY, AREA, Address, ManagerID, TEL, FAX, IsActive)
VALUES
('A00','台北總店','12345678','台北光學股份有限公司','100','台北市','中正區','台北市中正區忠孝東路一段1號','M0001','02-12345678','02-87654321',1),
('A01','台中分店','87654321','台中光學股份有限公司','400','台中市','西區','台中市西區公益路100號','M0002','04-12345678','04-87654321',1),
('A02','高雄分店','11223344','高雄光學股份有限公司','800','高雄市','新興區','高雄市新興區中山一路200號','M0003','07-12345678','07-87654321',1),
('A03','新竹分店','55667788','新竹光學股份有限公司','300','新竹市','東區','新竹市東區光復路300號','M0004','03-12345678','03-87654321',1),
('A04','台南分店','99887766','台南光學股份有限公司','700','台南市','中西區','台南市中西區民族路400號','M0005','06-12345678','06-87654321',1),
('A05','宜蘭分店','44556677','宜蘭光學股份有限公司','260','宜蘭縣','宜蘭市','宜蘭市中山路500號','M0006','03-98765432','03-23456789',1);


CREATE TABLE Staffs (
    StaffID VARCHAR(8) PRIMARY KEY,		-- 員工編號(門市代碼3碼+流水號5碼，新增儲存自動產生，編輯時不可變更)
    StoreID CHAR(3) NOT NULL,          	-- 所屬門市代號
    Name NVARCHAR(50) NOT NULL,        	-- 員工姓名
    Gender CHAR(1) CHECK (Gender IN ('M','F')), -- 男/女
    Position NVARCHAR(50),             	-- 職位 (例如 驗光師、助理)
    ZIPCode VARCHAR(20),             	-- 郵遞區號
	CITY VARCHAR(20),					-- 縣市	
	AREA VARCHAR(20),					-- 行政區域
	Address VARCHAR(200),				-- 詳細地址
    TEL VARCHAR(20),					-- 電話
    MOBILE VARCHAR(20),					-- 手機
    Email VARCHAR(100),					-- 電子郵件
    LineID VARCHAR(50),					-- LINE ID
    FacebookID VARCHAR(50),				-- Facebook ID
    Birthday DATE,						-- 生日
    BirthMonth INT CHECK (BirthMonth BETWEEN 1 AND 12),	-- 生日月份儲存時由Birthday自動取得
    Active BIT DEFAULT 1,              	-- 是否在職
    CreatedAt DATETIME DEFAULT GETDATE(),
    ModifiedAt DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (StoreID) REFERENCES Stores(StoreID),
);


-- 假設 Stores 資料表已有門市 001、002
-- 假設 ZIP 資料表已有 100 (台北市中正區)、400 (台中市西區)

INSERT INTO Staffs
(StaffID, StoreID, Name, Gender, Position, TEL, MOBILE, Email, LineID, FacebookID, Birthday, BirthMonth, ZIPCode, CITY, AREA, Address, Active, CreatedAt, ModifiedAt)
VALUES
('A0100001','A01','王小明','M','驗光師','0223456789','0912345678','ming@example.com','mingline','mingfb','1990-05-20',5,'100','台北市','中正區','忠孝東路一段1號',1,GETDATE(),GETDATE());

INSERT INTO Staffs
(StaffID, StoreID, Name, Gender, Position, TEL, MOBILE, Email, LineID, FacebookID, Birthday, BirthMonth, ZIPCode, CITY, AREA, Address, Active, CreatedAt, ModifiedAt)
VALUES
('A0100002','A01','李小華','F','助理','0223456790','0923456789','hua@example.com','hualine','huafb','1992-08-15',8,'100','台北市','中正區','忠孝東路一段2號',1,GETDATE(),GETDATE());

INSERT INTO Staffs
(StaffID, StoreID, Name, Gender, Position, TEL, MOBILE, Email, LineID, FacebookID, Birthday, BirthMonth, ZIPCode, CITY, AREA, Address, Active, CreatedAt, ModifiedAt)
VALUES
('A0200001','A02','陳大同','M','助理','0423456789','0933456789','tong@example.com','tongline','tongfb','1985-12-01',12,'400','台中市','西區','公益路100號',1,GETDATE(),GETDATE());


CREATE TABLE Users (
    UserID VARCHAR(10) PRIMARY KEY,        -- 使用者代號
    Username NVARCHAR(50) NOT NULL, 	   -- 登入帳號
    PasswordHash NVARCHAR(255) NOT NULL,   -- 密碼雜湊 (bcrypt/argon2)
    StoreID CHAR(3) NOT NULL,              -- 所屬門市
    RoleID INT NOT NULL,                   -- 角色代號
    IsActive BIT DEFAULT 1                 -- 是否啟用
);

CREATE TABLE Roles (
    RoleID INT IDENTITY(1,1) PRIMARY KEY,	-- 角色代號
    RoleName NVARCHAR(50) NOT NULL          -- 角色名稱 (店長/銷售員/會計/管理者)
);

CREATE TABLE Programs (
    ProgramID INT IDENTITY(1,1) PRIMARY KEY,
    ProgramName NVARCHAR(100) NOT NULL, -- 模組名稱 (例如：商品管理、銷售紀錄)
    ProgramCode NVARCHAR(50) NOT NULL  -- 模組代號 (唯一)
    Active BIT NOT NULL DEFAULT 1,      -- 是否啟用 (1=啟用, 0=停用)
    CreatedAt DATETIME NOT NULL DEFAULT GETDATE(), -- 建立時間
    ModifiedAt DATETIME NOT NULL DEFAULT GETDATE() -- 最後修改時間
);

CREATE TABLE RolePermissions (
    PermissionID INT IDENTITY(1,1) PRIMARY KEY,
    RoleID INT NOT NULL,
    ProgramID INT NOT NULL,
    CanCreate BIT NOT NULL DEFAULT 0,
    CanRead BIT NOT NULL DEFAULT 0,
    CanUpdate BIT NOT NULL DEFAULT 0,
    CanDelete BIT NOT NULL DEFAULT 0,
    CanReport BIT NOT NULL DEFAULT 0,
    FOREIGN KEY (RoleID) REFERENCES Roles(RoleID),
    FOREIGN KEY (ProgramID) REFERENCES Programs(ProgramID)
);


INSERT INTO Users (UserID, Username, PasswordHash, StoreID, RoleID, IsActive)
VALUES 
('admin','admin','$2y$10$YaEkSaZlsjia3Foju9qtguKJ7CNHjvklHVl3RmP/eS3bKqGlzoHxa','A00',5,1);

INSERT INTO Roles (RoleName) VALUES
(N'銷售員'),
(N'驗光師'),
(N'會計'),
(N'店長'),
(N'管理者');


INSERT INTO Programs (ProgramName, ProgramCode) VALUES
(N'商品管理', N'product'),
(N'銷售紀錄', N'sales'),
(N'庫存查詢', N'inventory'),
(N'會員管理', N'member'),
(N'報表分析', N'report');

-- 店長：商品管理、銷售紀錄、庫存查詢、會員管理、報表分析 (全權限)
INSERT INTO RolePermissions (RoleID, ProgramID, CanCreate, CanRead, CanUpdate, CanDelete, CanReport)
SELECT 1, ProgramID, 1,1,1,1,1 FROM Programs;

-- 銷售員：只能讀取商品管理、銷售紀錄、會員管理
INSERT INTO RolePermissions (RoleID, ProgramID, CanCreate, CanRead, CanUpdate, CanDelete, CanReport)
SELECT 2, ProgramID, 0,1,0,0,0 FROM Programs WHERE ProgramCode IN ('product','sales','member');

-- 會計：只能讀取報表分析
INSERT INTO RolePermissions (RoleID, ProgramID, CanCreate, CanRead, CanUpdate, CanDelete, CanReport)
SELECT 3, ProgramID, 0,1,0,0,1 FROM Programs WHERE ProgramCode='report';

-- 管理者：全模組全權限
INSERT INTO RolePermissions (RoleID, ProgramID, CanCreate, CanRead, CanUpdate, CanDelete, CanReport)
SELECT 4, ProgramID, 1,1,1,1,1 FROM Programs;


CREATE TABLE LoginAttempts (
    IP NVARCHAR(50) PRIMARY KEY,
    FailedCount INT DEFAULT 0,
    BlockUntil DATETIME NULL,
    IsBlocked BIT DEFAULT 0
);

CREATE TABLE LoginLogs (
    LogID INT IDENTITY(1,1) PRIMARY KEY,
    Username NVARCHAR(50),
    StoreID CHAR(3),
    IP NVARCHAR(50),
    AttemptTime DATETIME DEFAULT GETDATE(),
    Result NVARCHAR(20) -- 'Success' 或 'Failure'
);

CREATE TABLE SystemLogs (
    LogID        INT IDENTITY(1,1) PRIMARY KEY,  -- 自動流水號
    Username     NVARCHAR(50),    -- 哪個使用者操作NULL,
    StoreID      NVARCHAR(20),    -- 哪個店/系統環境
    ModuleName   NVARCHAR(100),   -- 哪個功能模組，例如 'Programs', 'Orders'
    Activity     NVARCHAR(100),   -- 操作類型: Login, Debug, Create, Edit, Delete
    Status       NVARCHAR(20),    -- 成功/失敗-- 原本 Result
    IPAddress    NVARCHAR(45),    -- 用戶端IP
    ErrorMessage NVARCHAR(1000),
    CreatedAt    DATETIME2 DEFAULT SYSDATETIME()
);




CREATE TABLE ProductCategories (		  -- 獨立管理商品類別與比率
    CategoryID INT IDENTITY(1,1) PRIMARY KEY,
    CategoryName NVARCHAR(50) NOT NULL,   -- 鏡框、鏡片、隱形眼鏡、藥水
    PointRate DECIMAL(5,2) NOT NULL,      -- 積分比率 (例如：1 元 = 1 點 → 1.00)
    AmountRate DECIMAL(5,2) NOT NULL      -- 消費金額比率 (例如：1 元 = 1 元 → 1.00)
);



CREATE TABLE MembershipLevels (			   -- 會員等級表
    LevelID INT IDENTITY(1,1) PRIMARY KEY, -- 會員等級編號
    LevelName NVARCHAR(50) NOT NULL,       -- 銀卡、金卡、白金卡、鑽石卡
    MinPoints INT DEFAULT 0,               -- 升級所需最低積分
    MinTotalSpent DECIMAL(12,2) DEFAULT 0, -- 升級所需最低累積消費金額
    BonusRate DECIMAL(5,2) DEFAULT 1.00    -- 額外積分加成 (例如：1.2 表示多 20%)
);

CREATE TABLE Customers (
    CustomerID CHAR(10) PRIMARY KEY, -- 門市代號(3碼)+流水號(7碼)
    StoreID CHAR(3) NOT NULL,        -- 所屬門市代號
    Name NVARCHAR(50) NOT NULL,		-- 姓名
    Gender CHAR(1) CHECK (Gender IN ('M','F')), -- 男/女
    Occupation NVARCHAR(50),		-- 職業
    PostalCode VARCHAR(6),			-- 郵遞區號
    City NVARCHAR(50),				-- 縣市
    District NVARCHAR(50),			-- 行政區域
    Address NVARCHAR(200),			-- 詳細地址
    Phone VARCHAR(20),				-- 電話
    Mobile VARCHAR(20),				-- 行動電話
    Email VARCHAR(100),				-- 電子郵件
    LineID VARCHAR(50),				-- LINE ID
    FacebookID NVARCHAR(50),		-- Facebook ID
    AcceptPromotion BIT DEFAULT 0,  -- 是否接受促銷訊息
    Birthday DATE,					-- 生日
    BirthMonth INT CHECK (BirthMonth BETWEEN 1 AND 12),	-- 生日月份
    Points INT DEFAULT 0,            		-- 消費積點
    TotalSpent DECIMAL(12,2) DEFAULT 0, 	-- 累積消費金額
    CreatedAt DATETIME DEFAULT GETDATE(),	-- 建檔時間
    MofifiedAt DATETIME DEFAULT GETDATE(),	-- 更新時間
	LevelID INT NULL,
    FOREIGN KEY (LevelID) REFERENCES MembershipLevels(LevelID)
);

CREATE TABLE EyeExamRecords (
    ExamID INT IDENTITY(1,1) PRIMARY KEY,
    CustomerID CHAR(10) NOT NULL,
    ExamDate DATE NOT NULL,
    Examiner NVARCHAR(50),           -- 驗光人員
    SphereRight DECIMAL(5,2),        -- 右眼球面度數
    CylinderRight DECIMAL(5,2),      -- 右眼散光度數
    AxisRight INT,                   -- 右眼散光軸位
	BaseCurveRight DECIMAL(5,2),	 -- 右眼角膜曲率
    PdRight DECIMAL(5,2),            -- 右眼瞳距
	AddRight  DECIMAL(5,2),			 -- 右眼加入度數	
    SphereLeft DECIMAL(5,2),         -- 左眼球面度數
    CylinderLeft DECIMAL(5,2),       -- 左眼散光度數
    AxisLeft INT,                    -- 左眼散光軸位
	BaseCurveLeft DECIMAL(5,2),	 	 -- 左眼角膜曲率
    PdLeft DECIMAL(5,2),             -- 左眼瞳距
	AddLeft  DECIMAL(5,2),			 -- 左眼加入度數	
    Notes NVARCHAR(200),             -- 其他備註
    CreatedAt DATETIME DEFAULT GETDATE(),
    ModifiedAt DATETIME NULL,
    FOREIGN KEY (CustomerID) REFERENCES Customers(CustomerID)
);

CREATE TABLE Products (
    ProductID VARCHAR(50) PRIMARY KEY,    -- 商品編碼 (類別1碼+品牌6碼+規格+顏色)
    ProductName NVARCHAR(100) NOT NULL,   -- 商品名稱
    Category CHAR(1) NOT NULL,            -- 商品類別 (A=鏡框, B=鏡片, C=隱形眼鏡, D=藥水, E=耗材)
    Brand NVARCHAR(50),
    Size NVARCHAR(50),
    Color NVARCHAR(50),
    Capacity NVARCHAR(50),                -- 容量 (藥水用)
    DefaultPurchasePrice DECIMAL(10,2),   -- 預設進貨價格
    DefaultSalePrice DECIMAL(10,2),       -- 預設銷售價格
    CreatedAt DATETIME DEFAULT GETDATE(),
    ModifiedAt DATETIME NULL
);

CREATE TABLE BatchNumbers (
    BatchID CHAR(11) PRIMARY KEY,         -- 批序號 (門市代號3碼 + 類別1碼 + 流水號7碼)
    StoreID CHAR(3) NOT NULL,             -- 門市代號 (固定3碼，例如 S01、T02、N99)
    Category CHAR(1) NOT NULL,            -- 商品類別
    ProductID VARCHAR(50) NOT NULL,       -- 商品編碼 (共用)
    VendorBatchNo NVARCHAR(50) NULL,      -- 廠商批號 (隱形眼鏡/藥水/耗材用)
    Sphere DECIMAL(4,2) NULL,             -- 球面度數 (鏡片/隱形眼鏡用)
    Cylinder DECIMAL(4,2) NULL,           -- 散光度數 (鏡片/隱形眼鏡用)
    Axis INT NULL,                        -- 散光角度 (鏡片/隱形眼鏡用)
    Addition DECIMAL(4,2) NULL,           -- 加入度 (鏡片/隱形眼鏡用)
    PD DECIMAL(4,2) NULL,                 -- 瞳距 (鏡片/隱形眼鏡用)
    Curvature DECIMAL(4,2) NULL,          -- 瞳孔弧度 (隱形眼鏡用)
    Processing NVARCHAR(200) NULL,        -- 加工內容/說明 (鏡片用：染色、鍍膜、裁切、拋光等)
    StockQty INT DEFAULT 0,               -- 庫存數量 (每門市獨立)
    CreatedAt DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (ProductID) REFERENCES Products(ProductID)
);

-- 商品主檔
INSERT INTO Products (ProductID, ProductName, Category, Brand, Size, Color, DefaultPurchasePrice, DefaultSalePrice)
VALUES
('A-RAYBAN-55MM-BLACK','鏡框A款','A','RayBan','55MM','黑色',1500,2000),
('B-HOYA-55MM-CLEAR','鏡片標準型','B','HOYA','55MM','透明',1000,1500),
('C-ACUVUE-14MM-CLEAR','隱形眼鏡日拋','C','Acuvue','14MM','透明',400,500);

-- 批序號表 (不同門市庫存)
INSERT INTO BatchNumbers (BatchID,StoreID,Category,ProductID,StockQty)
VALUES
('S01A0000001','S01','A','A-RAYBAN-55MM-BLACK',10),
('S02A0000001','S02','A','A-RAYBAN-55MM-BLACK',5),
('S01B0000001','S01','B','B-HOYA-55MM-CLEAR',20),
('S01C0000001','S01','C','C-ACUVUE-14MM-CLEAR',50);


CREATE TABLE Transactions (
    TransactionID CHAR(12) PRIMARY KEY,   -- 自動產生：StoreID + 流水號(8位)
    StoreID CHAR(4) NOT NULL,             -- 門市編號
    CustomerID CHAR(10) NOT NULL,         -- 外鍵：顧客
    SalespersonID CHAR(10) NOT NULL,      -- 外鍵：業務員
    ExamID INT NULL,                      -- 外鍵：驗光單號
    TransactionDate DATETIME DEFAULT GETDATE(),
    TotalAmount DECIMAL(12,2) DEFAULT 0,  -- 合計金額
    ActualPayment DECIMAL(12,2) DEFAULT 0,-- 實際收款
    TotalPointsEarned INT DEFAULT 0,      -- 本次消費積分
    CreatedAt DATETIME DEFAULT GETDATE(),
    ModifiedAt DATETIME NULL,
    FOREIGN KEY (CustomerID) REFERENCES Customers(CustomerID),
    FOREIGN KEY (SalespersonID) REFERENCES Staffs(StaffID),
    FOREIGN KEY (ExamID) REFERENCES EyeExamRecords(ExamID)
);

CREATE TABLE TransactionDetails (
    DetailID INT IDENTITY(1,1) PRIMARY KEY,
    TransactionID CHAR(12) NOT NULL,       -- 外鍵：交易主檔
    ProductID CHAR(10) NOT NULL,           -- 外鍵：產品
    BatchNo NVARCHAR(50) NULL,             -- 批序號
    Quantity INT NOT NULL,
    UnitPrice DECIMAL(10,2) NOT NULL,      -- 定價
    SalePrice DECIMAL(10,2) NOT NULL,      -- 實際銷售金額
    SubTotal AS (Quantity * SalePrice) PERSISTED,
    PointsEarned INT DEFAULT 0,            -- 該商品積分 (依商品類別計算)
    FOREIGN KEY (TransactionID) REFERENCES Transactions(TransactionID),
    FOREIGN KEY (ProductID) REFERENCES Products(ProductID)
);


INSERT INTO Customers (CustomerID, Name, Phone) VALUES
('CUST0001','王小明','0912345678'),
('CUST0002','李美麗','0922333444'),
('CUST0003','張大偉','0933555666');

INSERT INTO Staffs (StaffID, Name, StoreID) VALUES
('EMP001','陳業務','S001'),
('EMP002','林銷售','S001');

INSERT INTO Products (ProductID, ProductName, Category, UnitPrice) VALUES
('P001','隱形眼鏡日拋','Lens',500),
('P002','鏡框A款','Frame',2000),
('P003','清潔液500ml','Other',300);

-- 第一筆交易 (S00100000001)
INSERT INTO TransactionDetails (TransactionID, ProductID, BatchNo, Quantity, UnitPrice, SalePrice, PointsEarned)
VALUES
('S00100000001','P001','BATCH001',2,500,500,20),   -- Lens: 1000元 → 20點
('S00100000001','P002','BATCH002',1,2000,1800,10); -- Frame: 1800元 → 10點

-- 第二筆交易 (S00100000002)
INSERT INTO TransactionDetails (TransactionID, ProductID, BatchNo, Quantity, UnitPrice, SalePrice, PointsEarned)
VALUES
('S00100000002','P003','BATCH003',2,300,250,4),    -- Other: 500元 → 2點 (每200元1點)
('S00100000002','P001','BATCH004',2,500,500,20);   -- Lens: 1000元 → 20點

INSERT INTO Transactions (TransactionID, StoreID, CustomerID, SalespersonID, ExamID, TransactionDate, TotalAmount, ActualPayment, TotalPointsEarned)
VALUES
('S00100000001','S001','CUST0001','EMP001',NULL,'2026-03-16',2800,2800,30),
('S00100000002','S001','CUST0002','EMP002',NULL,'2026-03-16',2300,2300,24);

CREATE TRIGGER trg_CalcPoints
ON Transactions
AFTER INSERT
AS
BEGIN
    UPDATE t
    SET PointsEarned = CAST(t.Amount * pc.PointRate AS INT),
        Amount = t.Amount * pc.AmountRate
    FROM Transactions t
    JOIN inserted i ON t.TransactionID = i.TransactionID
    JOIN ProductCategories pc ON t.CategoryID = pc.CategoryID;

    UPDATE Customers
    SET Points = Points + t.PointsEarned,
        TotalSpent = TotalSpent + t.Amount
    FROM Customers c
    JOIN Transactions t ON c.CustomerID = t.CustomerID
    JOIN inserted i ON t.TransactionID = i.TransactionID;
END;

CREATE TRIGGER trg_UpdateMembershipLevel
ON Transactions
AFTER INSERT
AS
BEGIN
    -- 更新積分與消費金額 (已在前一個 Trigger 完成)
    -- 檢查會員等級
    UPDATE c
    SET LevelID = (
        SELECT TOP 1 LevelID
        FROM MembershipLevels ml
        WHERE c.Points >= ml.MinPoints OR c.TotalSpent >= ml.MinTotalSpent
        ORDER BY ml.MinPoints DESC, ml.MinTotalSpent DESC
    )
    FROM Customers c
    JOIN inserted i ON c.CustomerID = i.CustomerID;
END;


