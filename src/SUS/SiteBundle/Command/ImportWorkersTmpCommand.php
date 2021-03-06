<?php
namespace SUS\SiteBundle\Command;

use Symfony\Component\Finder\Finder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use SUS\SiteBundle\Entity\Unit;
use SUS\SiteBundle\Entity\Workers;

class ImportWorkersTmpCommand extends ContainerAwareCommand
{
    protected function configure()
    {

        $this
            ->setName('sus:importworkerstmp')
            ->setDescription('Import a CSV with line data')
            ->addOption('file', null, InputOption::VALUE_REQUIRED, 'xls file to import from')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting ImportCSV process');
        $this->container = $this->getContainer();
        $this->em = $this->container->get('doctrine')->getManager();
//        $this->pdo = new \PDO('mysql:host=localhost;dbname=mmsch;charset=utf8', 'root', '');
        $this->cvsParsingOptions = array(
            'ignoreFirstLine' => true
        );
        $xls = $this->parseCSV($input->getOption('file'));
        $headersRow = $xls->getRowIterator(1)->current();
        $headers = $this->parseHeadersToArray($headersRow);
        foreach ($xls->getRowIterator(2) as $row) {
            $toFlush = array();
            $fields = $this->parseRowToArray($row, $headers);
            $unit = $this->em->getRepository('SUS\SiteBundle\Entity\Unit')->findOneBy(array(
                 'name' => ('ΙΕΚ '.$fields['Δ.Ι.Ε.Κ.']),
            ));
            if(!isset($unit)) {
                $output->writeln('Skipping unit: '.$fields['MM_id']);
                continue;
            }

            if(trim($fields['ΝΕΟΣ ΔΙΕΥΘΥΝΤΗΣ ΑΠΌ ΠΡΟΚΗΡΥΞΗ']) == '') { continue; }
            $names = explode(' ', $fields['ΝΕΟΣ ΔΙΕΥΘΥΝΤΗΣ ΑΠΌ ΠΡΟΚΗΡΥΞΗ']);
            $worker = $this->em->getRepository('SUS\SiteBundle\Entity\Workers')->findOneBy(array(
                'lastname' => $names[0],
                'firstname' => (isset($names[1]) ? $names[1] : null),
            ));
            if(!isset($worker)) {
                $worker = new Workers();
                $output->writeln('Worker added: '.$fields['ΝΕΟΣ ΔΙΕΥΘΥΝΤΗΣ ΑΠΌ ΠΡΟΚΗΡΥΞΗ']);
            } else {
                $output->writeln('Worker found: '.$fields['ΝΕΟΣ ΔΙΕΥΘΥΝΤΗΣ ΑΠΌ ΠΡΟΚΗΡΥΞΗ']);
            }
            $worker->setLastname($names[0]);
            if(isset($names[1])) { $worker->setFirstname($names[1]); }
            $worker->setUnit($unit);
            $unit->setManager($worker);
            $toFlush[] = $worker;
            $toFlush[] = $unit;

            // Υποδιευθυντές
            foreach(explode('2.', $fields['ΥΠΟΔΙΕΥΘΥΝΤΗΣ']) as $curName) {
               if(trim(trim($curName), '1.') == '') { continue; }
               $names = explode(' ', trim(trim($curName), '1.'));
               $subworker = $this->em->getRepository('SUS\SiteBundle\Entity\Workers')->findOneBy(array(
                   'lastname' => $names[0],
                   'firstname' => (isset($names[1]) ? $names[1] : null),
               ));
               if(!isset($subworker)) {
                   $subworker = new Workers();
                   $output->writeln('Subworker added: '.trim(trim($curName), '1.'));
               } else {
                   $output->writeln('Subworker found: '.trim(trim($curName), '1.'));
               }
               $subworker->setLastname($names[0]);
               if(isset($names[1])) { $subworker->setFirstname($names[1]); }
               $unit->getResponsibles()->add($subworker);
               $subworker->getResponsibleUnits()->add($unit);
               $this->em->persist($subworker);
               $toFlush[] = $subworker;
            }

            $this->em->persist($worker);
            $this->em->flush($toFlush);
        }

        $output->writeln('Workers imported successfully');
    }

    private function parseHeadersToArray($headersRow) {
        $cellIterator = $headersRow->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false); 
        $result = array();
        foreach ($cellIterator as $cell) {
            $result[] = $cell->getValue();
        }
        return $result;
    }

    private function parseRowToArray($row, $headers) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false); 
        $result = array();
        $i = 0;
        foreach ($cellIterator as $cell) {
            $result[$headers[$i]] = $cell->getValue();
            $i++;
        }
        return $result;
    }

    private function parseCSV($file)
    {
        $ignoreFirstLine = $this->cvsParsingOptions['ignoreFirstLine'];

        $finder = new Finder();
        $finder->files()->in(dirname($file))->name(basename($file));
        ;
        foreach ($finder as $file) { $csv = $file; }

        $phpExcelObject = $this->getContainer()->get('xls.load_xls2007')->load($csv->getRealPath());
        $sheet = $phpExcelObject->getSheet(0);
        //$objReader = PHPExcel_IOFactory::createReader($inputFileType);
        return $sheet;
    }
}
