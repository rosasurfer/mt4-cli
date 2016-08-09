<?php
namespace rosasurfer\myfx\lib\myfxbook;


// data indices of account history in CSV statements
const I_CSV_OPEN_DATE          =  0;         // Open Date                     '08/08/2016 17:33'      '08/08/2016 01:13'
const I_CSV_CLOSE_DATE         =  1;         // Close Date                    '08/08/2016 22:23'      ''
const I_CSV_SYMBOL             =  2;         // Symbol                        'GBPUSD'                ''
const I_CSV_ACTION             =  3;         // Action                        'Buy|Sell'              'Deposit|Withdrawal'
const I_CSV_LOTS               =  4;         // Lots                          '0.20'                  '0.01'
const I_CSV_STOP_LOSS          =  5;         // SL                            '0.00000'               '0.00000'
const I_CSV_TAKE_PROFIT        =  6;         // TP                            '0.00000'               '0.00000'
const I_CSV_OPEN_PRICE         =  7;         // Open Price                    '1.30377'               '0.00000'
const I_CSV_CLOSE_PRICE        =  8;         // Close Price                   '1.30451'               '0.00000'
const I_CSV_COMMISSION         =  9;         // Commission                    '-1.5100'               '0.0000'
const I_CSV_SWAP               = 10;         // Swap                          '0.0000'                '0.0000'
const I_CSV_PIPS               = 11;         // Pips                          '7.4'                   '0.0'
const I_CSV_NET_PROFIT         = 12;         // Profit                        '13.29'                 '2000.00'
const I_CSV_GAIN               = 13;         // Gain                          '0.07'                  '0'
const I_CSV_DURATION_TIME      = 14;         // Duration (DD:HH:MM:SS)        '00:04:50:05'           '00:00:00:00'
const I_CSV_PROFITABLE_PCT     = 15;         // Profitable(%)                 '69.7'                  ''
const I_CSV_PROFITABLE_TIME    = 16;         // Profitable(time duration)     '3h 22m'                ''
const I_CSV_DRAWDOWN           = 17;         // Drawdown                      '18.0'                  ''
const I_CSV_RISK_REWARD        = 18;         // Risk:Reward                   '1.17'                  ''
const I_CSV_MAX_PIPS           = 19;         // Max(pips)                     '8.3'                   ''
const I_CSV_MAX_USD            = 20;         // Max(USD)                      '16.6'                  ''
const I_CSV_MIN_PIPS           = 21;         // Min(pips)                     '-9.7'                  ''
const I_CSV_MIN_USD            = 22;         // Min(USD)                      '-19.4'                 ''
const I_CSV_ENTRY_ACCURACY_PCT = 23;         // Entry Accuracy(%)             '46.1'                  ''
const I_CSV_EXIT_ACCURACY_PCT  = 24;         // Exit Accuracy(%)              '95.0'                  ''
const I_CSV_PROFIT_MISSED_PIPS = 25;         // ProfitMissed(pips)            '-0.90'                 ''
const I_CSV_PROFIT_MISSED_USD  = 26;         // ProfitMissed(USD)             '-1.80'                 ''
