<?php


namespace PHPBundler;


define('ROOT', __DIR__.'/..');

define('BASE', ROOT."/PHPBundler");


class Engine {

    protected $config;
    
    protected $base;
    
    protected $entry;
    
    protected $packet;
    
    protected $output;

    protected $template;

    protected $watch;

    
    /* MAIN CONSTRUCTOR
     *
     * @none : (void|process)
     *
     */
    public function __construct( $config=array() )
    {
        $this->_config_preparation();

        $this->config = array_merge($this->config, $config);
        
        $ENTRY = $this->config['entry'];
        
        if (is_array($ENTRY ) ){
            if (isset($ENTRY[0] ) ){
                foreach ($ENTRY as $entry) {

                    $this->_bundling($entry['source'], $entry['output'], $entry['bundle'] );
                }
            }else {

                $this->_bundling($ENTRY['source'], $ENTRY['output'], $ENTRY['bundle'] );
            }
        }else {

            $this->_bundling($this->config['source'], $this->config['output'], $this->config['bundle'] );
        }
    }


    /* SETUP PREPARATIONS
     *
     * @none : (void|process)
     *
     */
    public function _config_preparation()
    {
        $template = array();
        $template['plate'] = '';
        $template['class'] = '';
        $this->template = $template;
        
        $this->_preparation('base');
        
        $this->_preparation('packet');

        $this->_preparation('watch');
        
        $config = array();
        $config['serve'] = FALSE;
        $config['entry'] = FALSE;
        $config['module'] = [];
        $config['source'] = 'src/*.*';
        $config['output'] = 'dist/index.php';
        $config['bundle'] = 'class';
        $config['bundle'] = 'class';
        $this->config = $config;
    }

    public function _preparation( $name )
    {
        if ($name=='base' ) {
            $this->base = [];
            $base = array(); // preparation base languange
            $base[] = 'php';
            $base[] = 'css';
            $base[] = 'js';
            $base[] = 'html';
            $this->base = $base;
        }
        if ($name=='entry') {
            $this->entry = [];
        }
        if ($name=='packet' ) {
            $this->packet = [];
            $packet = array();
            $packet['php'] = array();
            $packet['css'] = array();
            $packet['js'] = array();
            $packet['html']['head'] = array();
            $packet['html']['body'] = array();
            $this->packet = $packet;
        }
        if ($name=='output') {
            $this->entry = [];
        }
        if ($name=='watch') {
            $this->watch = [];
            $this->watch['baseurl'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].str_replace('?','',$_SERVER['REQUEST_URI']);
            $this->watch['size'] = 0;
        }
    }


    /* SOURCE HANDLING
     *
     * @source, data : (contents|source)
     *
     */
    public function _source( $source, $data=FALSE )
    {
        if ($data!=FALSE) {
            return file_put_contents($source, $data );
        }else {
            return file_get_contents($source );
        }
    }


    /* GET ENTRY POINT
     *
     * @path, extension : (true|target)
     *
     */
    public function _entry( $source, $extension='' )
    {
        $TARGET = ROOT.'/'.$source;
        $FILE = glob($TARGET.$extension);
        $LIST = array();
        foreach ($FILE as $key=>$val) {
            $this->entry[pathinfo($val, PATHINFO_EXTENSION)][] = $val;
        }
        return true;
    }



    /* ENC/DEC CRYPTION
     *
     * @packet, state : (encode|decode)
     *
     */
    private function _cryption( $packet, $state )
    {
        if ($state==TRUE) {
            return base64_encode($packet);
        }else {
            return base64_decode($packet);
        }
    }



    /* GET PACKET SOURCE
     *
     * @base, source : (void|process)
     *
     */
    public function _packet( $base, $source )
    {   
        if ($base=='php') {
            $x = $this->_source($source);
            $x = $this->_packet_filter($x, $base);
            $x = $this->_cryption($x, TRUE );
            $this->packet[$base][] = $x;
        }
        if ($base=='css') {
            $x = $this->_source($source);
            $x = $this->_packet_filter($x, $base);
            $x = $this->_cryption($x, TRUE );
            $this->packet[$base][] = $x;
        }
        if ($base=='js') {
            $x = $this->_source($source);
            $x = $this->_packet_filter($x, $base);
            $x = $this->_cryption($x, TRUE );
            $this->packet[$base][] = $x;
        }
        if ($base=='html') {
            $x = $this->_source($source);
            if (strpos($x,"DOCTYPE")==false ){
                if ( (strpos($x,"<head")==FALSE) && (strpos($x,"<body")==FALSE) ) {
                    if ( (strpos($x,"<head")!==FALSE) || (strpos(pathinfo($source, PATHINFO_FILENAME),'.head')!==FALSE) ){
                        $x = $this->_packet_filter($x, 'head');
                        $x = $this->_cryption($x, TRUE );
                        $this->packet[$base]['head'][] = $x;
                    }
                    if ( (strpos($x,"<body")!==FALSE) || (strpos(pathinfo($source, PATHINFO_FILENAME),'.body')!==FALSE) ){
                        $x = $this->_packet_filter($x, 'body');
                        $x = $this->_cryption($x, TRUE );
                        $this->packet[$base]['body'][] = $x;
                    }
                }
            }
        }
    }

    
    /* CHAIN PACKET FOR FILTER
     *
     * @ x, base : (x|target)
     *
     */
    public function _packet_filter( $x, $base )
    {
        if ($base=='php') {
            $x = str_replace("<?php\n",null,$x);
            $x = str_replace("<?php",null,$x);
            $x = str_replace("\n?>",null,$x);
            $x = str_replace("?>",'',$x);
            return $x;
        }
        if ($base=='head') {
            $x = str_replace("<head>\n",null,$x);
            $x = str_replace("<head>",null,$x);
            $x = str_replace("\n</head>",null,$x);
            $x = str_replace("</head>",null,$x);
            return $x;
        }
        if ($base=='body') {
            $x = str_replace("<body>\n",null,$x);
            $x = str_replace("<body>",null,$x);
            $x = str_replace("\n</body>",null,$x);
            $x = str_replace("</body>",null,$x);
            return $x;
        }
        if ($base=='css') {
            $x = str_replace("<style>\n",'',$x);
            $x = str_replace("<style>",'',$x);
            $x = str_replace("\n</style>",'',$x);
            $x = str_replace("</style>",'',$x);
            return $x;
        }
        if ($base=='js') {
            $x = str_replace("<script>\n",'',$x);
            $x = str_replace("<script>",'',$x);
            $x = str_replace("\n</script>",'',$x);
            $x = str_replace("</script>",'',$x);
            return $x;
        }
    }



    /* OUTPUT SOURCE TO FILE
     *
     * @ template, packet : (void|process)
     *
     */
    public function _output( $template='plate', $packet )
    {
        $TEMPLATE = BASE.'/template/'.$template.'.php';
        $this->output = $this->_source($TEMPLATE);

        if ($template=='plate') {
            $TEMPLATE = $this->_output_install('plate', 'PHP', $packet['php'], 0);
            $TEMPLATE = $this->_output_install('plate', 'CSS', $packet['css'], 2);
            $TEMPLATE = $this->_output_install('plate', 'JS', $packet['js'], 2);
            foreach ($packet['html'] as $base=>$packet) {
                $TEMPLATE = $this->_output_install('plate', 'HTML-'.$base, $packet, 1);
            }
        }

        if ($template=='class') {
            $TEMPLATE = $this->_output_install('class', 'PHP', $packet['php'], 2);
            $TEMPLATE = $this->_output_install('class', 'CSS', $packet['css'], 2);
            $TEMPLATE = $this->_output_install('class', 'JS', $packet['js'], 2);
            foreach ($packet['html'] as $base=>$packet) {
                $TEMPLATE = $this->_output_install('class', 'HTML-'.$base, $packet, 2);
            }
            $TEMPLATE = $this->_output_hash('APP');
            $TEMPLATE = $this->_output_hash('PHP');
            $TEMPLATE = $this->_output_hash('CSS');
            $TEMPLATE = $this->_output_hash('JS');
            $TEMPLATE = $this->_output_hash('HTML');
            $TEMPLATE = $this->_output_hash('CRYPTION');
            $TEMPLATE = $this->_output_hash('INSTALL');
        }    
    }

    /* CHAIN OUTPUT TO HASH
     *
     * @ prefix : (void|process)
     *
     */
    public function _output_hash( $prefix )
    {
        $encript = '_'.md5(uniqid());
        $this->output = str_replace("@{{ HASH-".strtoupper($prefix)." }}", $encript, $this->output);
    }

    /* CHAIN OUTPUT TO PACKING
     *
     * @ type, prefix, packet, template, extra : (void|process)
     *
     */
    public function _output_install( $type, $prefix, $packet, $extra=0 )
    {
        $EXTRA = str_repeat("    ",$extra);
        $SOURCE=[];
        $CLASS_PREFIX = '';
        if (strtoupper($prefix)=='HTML-HEAD') {
            $CLASS_PREFIX = '->head';
        }
        if (strtoupper($prefix)=='HTML-BODY') {
            $CLASS_PREFIX = '->body';
        }
        foreach ($packet as $source ){
            if ($type=='plate') {
                $SOURCE[] = str_replace("\n",$EXTRA, $this->_cryption($source, FALSE));
            }
            if ($type=='class') {
                $SOURCE[] = $EXTRA."\$bundle".$CLASS_PREFIX."[] = '".$source."';";
            }
        }
        if ($type=='plate') {
            $SOURCE = $EXTRA.implode("\n", $SOURCE);
        }
        if ($type=='class') {
            $SOURCE = implode("\n", $SOURCE);
        }
        
        $this->output = str_replace("@{{ ".strtoupper($prefix)." }}", $SOURCE, $this->output);
    }


    /* BUNDLING
     *
     * @ entry, output, bundle : (void|process)
     *
     */
    public function _bundling( $entry, $output, $bundle )
    {
        $this->_preparation('entry');
        $this->_preparation('packet');
        
        if (is_array($entry)) {
            foreach ($entry as $source) {
                $this->_entry($source);
            }
        }else {
            foreach ($this->base as $base ){
                $this->_entry('src/*.', $base);
            }
        }

        foreach ($this->entry as $base=>$loop ){
            foreach ($loop as $source ){
                $this->_packet($base, $source);
            }
        }

        $this->_watch($this->entry);
        $this->_output($bundle, $this->packet);
        if (file_exists($output)) {
            unlink($output);
        }
        
        $_inserted_ = $this->_source($output, $this->output);
        if ($_inserted_) {
            $SERVE = $this->_serve();
            @ eval("?> ".$SERVE." <?php");
        }
    }

    /* WATCH
     *
     * @ entry : (void|process)
     *
     */
    public function _watch( $entry )
    {
        foreach ($this->entry as $base=>$loop ){
            foreach ($loop as $source ){
                
                $this->watch['size'] += filesize($source);
            }
        }
    }

    /* SERVE
     *
     * @ void : (void|process)
     *
     */
    public function _serve()
    {
        if ($this->config['serve'] ) {
            /*eval("?> ".$this->_source($this->config['serve'] )." <?php"); */
            if (isset($_GET['watch'])) {
                
                header("Content-Type: text/event-stream");
                header("Cache-Control: no-cache");
                
                $this->_preparation('watch');
                $this->_watch($this->entry);
                echo "event: watch\n";
                echo "data: ".$this->watch['size']."\n\n";
                ob_flush();
                flush();
            }else {
                
                return $this->_source(BASE.'/template/serve.php' );
                /* @ eval("?> ".$this->_source(BASE.'/template/serve.php' )." <?php"); */
            }
        }
    }
}