<?php

/**
 * This is the model class for table "ads".
 *
 * The followings are the available columns in table 'ads':
 * @property integer $id
 * @property integer $category_id
 * @property integer $city_id
 * @property integer $user_id
 * @property integer $type_id
 * @property string $title
 * @property string $details
 * @property string $price
 * @property string $author
 * @property string $email
 * @property string $phone
 * @property string $address
 * @property integer $date_pub
 * @property integer $date_end
 * @property integer $checked
 * @property integer $views
 * @property integer $status
 *
 * The followings are the available model relations:
 * @property Types $type
 * @property BoardCategory $category
 * @property Cities $city
 * @property Users $user
 * @property AdsImg[] $adsImgs
 */
class Ads extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Ads the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'ads';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('category_id, city_id, user_id, type_id, title, details, author, email, phone, date_pub, date_end, views', 'required', 'on'=>'create'),
			array('category_id, city_id, user_id, type_id, date_pub, date_end, checked, views, status', 'numerical', 'integerOnly'=>true),
			array('title, author, phone', 'length', 'max'=>255),
			array('price', 'length', 'max'=>8),
			array('email', 'length', 'max'=>400),
            array('email', 'email'),
            array('address','safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, category_id, city_id, user_id, type_id, title, details, price, author, email, phone, address, date_pub, date_end, checked, views, status', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'type' => array(self::BELONGS_TO, 'Types', 'type_id'),
			'category' => array(self::BELONGS_TO, 'BoardCategory', 'category_id'),
			'city' => array(self::BELONGS_TO, 'Cities', 'city_id'),
			'user' => array(self::BELONGS_TO, 'Users', 'user_id'),
			'adsImgs' => array(self::HAS_MANY, 'AdsImg', 'ads_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'category_id' => 'Категория',
			'city_id' => 'Город',
			'user_id' => 'Пользователь',
			'type_id' => 'Я хочу',
			'title' => 'Заголовок',
			'details' => 'Дополнительные сведения',
			'price' => 'Цена',
			'author' => 'Контактное лицо',
			'email' => 'Email',
			'phone' => 'Телефон',
			'address' => 'Адрес',
			'date_pub' => 'Дата публикации',
			'date_end' => 'Дата окончания',
			'checked' => 'Проверено',
			'views' => 'Просмотров',
			'status' => 'Публикация',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('category_id',$this->category_id);
		$criteria->compare('city_id',$this->city_id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('type_id',$this->type_id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('details',$this->details,true);
		$criteria->compare('price',$this->price,true);
		$criteria->compare('author',$this->author,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('date_pub',$this->date_pub);
		$criteria->compare('date_end',$this->date_end);
		$criteria->compare('checked',$this->checked);
		$criteria->compare('views',$this->views);
		$criteria->compare('status',$this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    public function afterSave( )
    {
        //Если новая запись, сохраняем к ней картинки
        if($this->isNewRecord)
        {
            $this->addImages();
        }

        parent::afterSave();
    }

    public function addImages()
    {
        //Если мы в ожидании изображения
        if( Yii::app()->user->hasState('images') )
        {
            $userImages = Yii::app()->user->getState('images');
            //Решение окончательное путь для изображений
            $path = Yii::app( )->getBasePath( )."/../content/board/uploads/";
            //Создайте папку и дать разрешение, если он не существует
            if( !is_dir( $path ) )
            {
                mkdir( $path );
                chmod( $path, 0777 );
            }

            //Теперь создадим соответствующие модели и переместите файлы
            foreach( $userImages as $image )
            {
                if( is_file( $image["path"] ) )
                {
                    if( rename( $image["path"], $path.$image["filename"] ) )
                    {
                        rename( $image["thumb"], $path.'thumbs/'.$image["filename"]);
                        chmod( $path.$image["filename"], 0777 );
                        $img = new AdsImg();

                        $img->img = "/content/board/uploads/".$image["filename"];
                        $img->thumb = "/content/board/uploads/thumbs/".$image["filename"];
                        $img->ads_id = $this->id;
                        $img->status = 1;
                        if( !$img->save( ) )
                        {
                            //Это всегда хорошо, чтобы log что-то
                            Yii::log( "Could not save Image:\n".CVarDumper::dumpAsString(
                                $img->getErrors( ) ), CLogger::LEVEL_ERROR );
                            //Такое исключение откат транзакции
                            throw new Exception( 'Could not save Image');
                        }
                    }
                }
                else
                {
                    //Вы также можете бросить Execption здесь, чтобы откат транзакции
                    Yii::log( $image["path"]." is not a file", CLogger::LEVEL_WARNING );
                }
            }
            //Чистая сессия пользователя
            Yii::app( )->user->setState( 'images', null );
        }
    }
}