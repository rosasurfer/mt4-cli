<?php
namespace rosasurfer\myfx\metatrader\model;

use rosasurfer\dao\PersistableObject;
use rosasurfer\exception\IllegalTypeException;
use rosasurfer\exception\InvalidArgumentException;


/**
 * Represents a MetaTrader order ticket.
 */
class Order extends PersistableObject {


   /** @var int - ticket number */
   protected $ticket;

   /** @var int - order type */
   protected $type;

   /** @var float - lot size */
   protected $lots;

   /** @var string - symbol */
   protected $symbol;

   /** @var float - open price */
   protected $openPrice;

   /** @var string - open time (server timezone) */
   protected $openTime;

   /** @var float - stoploss price */
   protected $stopLoss;

   /** @var float - takeprofit price */
   protected $takeProfit;

   /** @var float - close price */
   protected $closePrice;

   /** @var string - close time (server timezone) */
   protected $closeTime;

   /** @var float - commission */
   protected $commission;

   /** @var float - swap */
   protected $swap;

   /** @var float - gross profit */
   protected $profit;

   /** @var int - magic number */
   protected $magicNumber;

   /** @var string - order comment */
   protected $comment;

   /** @var Test - the Test the Order belongs to */
   protected $test;

   /** @var int - the Test's id */
   protected $test_id;


   /**
    * Create a new Order.
    *
    * @return self
    */
   public static function create(Test $test, array $properties) {
      $order          = new static();
      $order->test    = $test;
      $order->test_id = $test->getId();

      $id = $properties['id'];
      if (!is_int($id))                                      throw new IllegalTypeException('Illegal type of property "id": '.getType($id));
      if ($id)                                               throw new InvalidArgumentException('Invalid property "id": '.$id.' (not zero)');
      $order->id = null;

      $ticket = $properties['ticket'];
      if (!is_int($ticket))                                  throw new IllegalTypeException('Illegal type of property "ticket": '.getType($ticket));
      if ($ticket <= 0)                                      throw new InvalidArgumentException('Invalid property "ticket": '.$ticket.' (not positive)');
      $order->ticket = $ticket;

      $type = $properties['type'];
      if (!is_int($type))                                    throw new IllegalTypeException('Illegal type of property "type": '.getType($type));
      if (!\MT4::isOrderType($type))                         throw new InvalidArgumentException('Invalid property "type": '.$type.' (not an order type)');
      $order->type = $type;

      $lots = $properties['lots'];
      if (!is_float($lots))                                  throw new IllegalTypeException('Illegal type of property "lots": '.getType($lots));
      if ($lots <= 0)                                        throw new InvalidArgumentException('Invalid property "lots": '.$lots.' (not positive)');
      if ($lots != round($lots, 2))                          throw new InvalidArgumentException('Invalid property "lots": '.$lots.' (lot step violation)');
      $order->lots = $lots;

      $symbol = $properties['symbol'];
      if (!is_string($symbol))                               throw new IllegalTypeException('Illegal type of property "symbol": '.getType($symbol));
      if ($symbol != trim($symbol))                          throw new InvalidArgumentException('Invalid property "symbol": "'.$symbol.'" (format violation)');
      if (!strLen($symbol))                                  throw new InvalidArgumentException('Invalid property "symbol": "'.$symbol.'" (length violation)');
      if (strLen($symbol) > \MT4::MAX_SYMBOL_LENGTH)         throw new InvalidArgumentException('Invalid property "symbol": "'.$symbol.'" (length violation)');
      $order->symbol = $symbol;

      $openPrice = $properties['openPrice'];
      if (!is_float($openPrice))                             throw new IllegalTypeException('Illegal type of property "openPrice": '.getType($openPrice));
      $openPrice = round($openPrice, 5);
      if ($openPrice <= 0)                                   throw new InvalidArgumentException('Invalid property "openPrice": '.$openPrice.' (not positive)');
      $order->openPrice = $openPrice;

      $openTime = $properties['openTime'];
      if (!is_int($openTime))                                throw new IllegalTypeException('Illegal type of property "openTime": '.getType($openTime));
      if ($openTime <= 0)                                    throw new InvalidArgumentException('Invalid property "openTime": '.$openTime.' (not positive)');
      if (!isForexTradingDay($openTime, 'FXT'))              throw new InvalidArgumentException('Invalid property "openTime": '.$openTime.' (not a trading day)');
      $order->openTime = $openTime;

      $stopLoss = $properties['stopLoss'];
      if (!is_float($stopLoss))                              throw new IllegalTypeException('Illegal type of property "stopLoss": '.getType($stopLoss));
      $stopLoss = round($stopLoss, 5);
      if ($stopLoss < 0)                                     throw new InvalidArgumentException('Invalid property "stopLoss": '.$stopLoss.' (not non-negative)');
      $order->stopLoss = !$stopLoss ? null : $stopLoss;

      $takeProfit = $properties['takeProfit'];
      if (!is_float($takeProfit))                            throw new IllegalTypeException('Illegal type of property "takeProfit": '.getType($takeProfit));
      $takeProfit = round($takeProfit, 5);
      if ($takeProfit < 0)                                   throw new InvalidArgumentException('Invalid property "takeProfit": '.$takeProfit.' (not non-negative)');
      $order->takeProfit = !$takeProfit ? null : $takeProfit;

      if ($stopLoss && $takeProfit) {
         if (\MT4::isLongOrderType($order->type)) {
            if ($stopLoss >= $takeProfit)                    throw new InvalidArgumentException('Invalid properties "stopLoss|takeProfit" for LONG order: '.$stopLoss.'|'.$takeProfit.' (mis-match)');
         }
         else if ($stopLoss <= $takeProfit)                  throw new InvalidArgumentException('Invalid properties "stopLoss|takeProfit" for SHORT order: '.$stopLoss.'|'.$takeProfit.' (mis-match)');
      }

      $closePrice = $properties['closePrice'];
      if (!is_float($closePrice))                            throw new IllegalTypeException('Illegal type of property "closePrice": '.getType($closePrice));
      $closePrice = round($closePrice, 5);
      if ($closePrice < 0)                                   throw new InvalidArgumentException('Invalid property "closePrice": '.$closePrice.' (not non-negative)');
      $order->closePrice = !$closePrice ? null : $closePrice;

      $closeTime = $properties['closeTime'];
      if (!is_int($closeTime))                               throw new IllegalTypeException('Illegal type of property "closeTime": '.getType($closeTime));
      if ($closeTime < 0)                                    throw new InvalidArgumentException('Invalid property "closeTime": '.$closeTime.' (not positive)');
      if      ($closeTime && !$closePrice)                   throw new InvalidArgumentException('Invalid properties "closePrice|closeTime": '.$closePrice.'|'.$closeTime.' (mis-match)');
      else if (!$closeTime && $closePrice)                   throw new InvalidArgumentException('Invalid properties "closePrice|closeTime": '.$closePrice.'|'.$closeTime.' (mis-match)');
      if ($closeTime) {
         if (!isForexTradingDay($closeTime, 'FXT'))          throw new InvalidArgumentException('Invalid property "closeTime": '.$closeTime.' (not a trading day)');
         if ($closeTime < $openTime)                         throw new InvalidArgumentException('Invalid properties "openTime|closeTime": '.$openTime.'|'.$closeTime.' (mis-match)');
      }
      $order->closeTime = !$closeTime ? null : $closeTime;

      $commission = $properties['commission'];
      if (!is_float($commission))                            throw new IllegalTypeException('Illegal type of property "commission": '.getType($commission));
      $order->commission = round($commission, 2);

      $swap = $properties['swap'];
      if (!is_float($swap))                                  throw new IllegalTypeException('Illegal type of property "swap": '.getType($swap));
      $order->swap = round($swap, 2);

      $profit = $properties['profit'];
      if (!is_float($profit))                                throw new IllegalTypeException('Illegal type of property "profit": '.getType($profit));
      $order->profit = round($profit, 2);

      $magicNumber = $properties['magicNumber'];
      if (!is_int($magicNumber))                             throw new IllegalTypeException('Illegal type of property "magicNumber": '.getType($magicNumber));
      if ($magicNumber < 0)                                  throw new InvalidArgumentException('Invalid property "magicNumber": '.$magicNumber.' (not non-negative)');
      $order->magicNumber = !$magicNumber ? null : $magicNumber;

      $comment = $properties['comment'];
      if (!is_string($comment))                              throw new IllegalTypeException('Illegal type of property "comment": '.getType($comment));
      $comment = trim($comment);
      if (strLen($comment) > \MT4::MAX_ORDER_COMMENT_LENGTH) throw new InvalidArgumentException('Invalid property "comment": "'.$comment.'" (length violation)');
      $order->comment = strLen($comment) ? $comment : null;

      return $order;
   }


   /**
    * Whether or not this order ticket represents a position and not a pending order.
    *
    * @return bool
    */
   public function isPosition() {
      return (($this->type==OP_BUY || $this->type==OP_SELL) && $this->openTime > 0);
   }


   /**
    * Whether or not this ticket is closed.
    *
    * @return bool
    */
   public function isClosed() {
      return ($this->closeTime > 0);
   }


   /**
    * Whether or not this ticket represents a closed position.
    *
    * @return bool
    */
   public function isClosedPosition() {
      return ($this->isPosition() &&  $this->isClosed());
   }


   /**
    * Insert the instance into the database.
    *
    * @return self
    */
   protected function insert() {

      // create SQL

      // execute SQL

      // query instance id

      /*
      $created     =  $this->created;
      $ticket      =  $this->ticket;
      $closeprice  =  $this->closePrice;
      $stoploss    = !$this->stopLoss             ? 'null' : $this->stopLoss;
      $takeprofit  = !$this->takeProfit           ? 'null' : $this->takeProfit;
      $commission  =  is_null($this->commission ) ? 'null' : $this->commission;
      $swap        =  is_null($this->swap       ) ? 'null' : $this->swap;
      $profit      =  is_null($this->grossProfit) ? 'null' : $this->grossProfit;
      $magicnumber = !$this->magicNumber          ? 'null' : $this->magicNumber;
      $comment     =  is_null($this->comment)     ? 'null' : "'".addSlashes($this->comment)."'";

      $db = self::getDb();
      $db->begin();
      try {
         // insert instance
         $sql = "insert into t_closedposition (version, created, ticket, type, lots, symbol, opentime, openprice, closetime, closeprice, stoploss, takeprofit, commission, swap, profit, netprofit, magicnumber, comment, signal_id) values
                    ('$version', '$created', $ticket, '$type', $lots, '$symbol', '$opentime', $openprice, '$closetime', $closeprice, $stoploss, $takeprofit, $commission, $swap, $profit, $netprofit, $magicnumber, $comment, $signal_id)";
         $db->executeSql($sql);
         $result = $db->executeSql("select last_insert_id()");
         $this->id = (int) mysql_result($result['set'], 0);

         $db->commit();
      }
      catch (\Exception $ex) {
         $this->id = null;
         $db->rollback();
         throw $ex;
      }
      */
      return $this;
   }
}